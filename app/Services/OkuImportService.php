<?php

namespace App\Services;

use App\Models\Oku;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class OkuImportService
{
    public function __construct(private BesutResidenceService $residence) {}

    private const HEADERS = [
        'NAMA' => 'name',
        'NOMBOR KAD PENGENALAN' => 'ic_number',
        'JANTINA' => 'gender',
        'UMUR' => 'age',
        'STATUS PERKAHWINAN' => 'marital_status',
        'ALAMAT SURAT MENYURAT' => 'address',
        'NEGERI KEDIAMAN' => 'residential_state',
        'DAERAH KEDIAMAN' => 'residential_district',
        'MUKIM KEDIAMAN' => 'residential_mukim',
        'KAMPUNG ATAU KAWASAN' => 'residential_village',
        'POSKOD' => 'residential_postcode',
        'TARAF PENDIDIKAN' => 'education_level',
        'NOMBOR PENDAFTARAN OKU' => 'oku_card_number',
        'KATEGORI OKU' => 'oku_category',
        'SEKTOR PEKERJAAN' => 'employment_status',
        'NAMA PEKERJAAN' => 'job_name',
        'JENIS BANTUAN' => 'assistance_type',
    ];

    private const REQUIRED_FIELDS = [
        'name', 'ic_number', 'gender', 'age', 'marital_status', 'address', 'residential_state',
        'residential_district', 'residential_mukim',
        'residential_village', 'residential_postcode',
        'education_level', 'oku_card_number', 'oku_category', 'employment_status',
    ];

    public function import(string $path, string $extension): array
    {
        $rows = strtolower($extension) === 'xlsx'
            ? $this->readXlsx($path)
            : $this->readCsv($path);

        if ($rows === []) {
            throw new RuntimeException('Fail tidak mengandungi sebarang data.');
        }

        $headers = array_map(fn ($header) => $this->normaliseHeader($header), array_shift($rows));
        $mapping = [];
        foreach ($headers as $index => $header) {
            if (isset(self::HEADERS[$header])) {
                $mapping[$index] = self::HEADERS[$header];
            }
        }

        $missing = array_diff(self::REQUIRED_FIELDS, array_values($mapping));
        if ($missing !== []) {
            throw new RuntimeException('Format lajur tidak lengkap. Gunakan templat import MyOKUcare.');
        }

        $result = ['imported' => 0, 'duplicates' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            $number = $index + 2;
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = [];
            foreach ($mapping as $column => $field) {
                $data[$field] = trim((string) ($row[$column] ?? ''));
            }
            $data['job_name'] ??= null;
            $data['assistance_type'] ??= null;
            $data = $this->normaliseData($data);
            $isBesut = $this->residence->restrictedToBesut() || $this->residence->isBesutLocation($data);

            if (Oku::query()->withTrashed()->where('ic_number', $data['ic_number'])->orWhere('oku_card_number', $data['oku_card_number'])->exists()) {
                $result['duplicates']++;

                continue;
            }

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'ic_number' => ['required', 'string', 'max:20', 'unique:okus,ic_number'],
                'gender' => ['required', Rule::in(['Lelaki', 'Perempuan'])],
                'age' => ['required', 'integer', 'min:1', 'max:120'],
                'marital_status' => ['required', Rule::in(['Berkahwin', 'Bujang', 'Duda', 'Janda'])],
                'address' => ['required', 'string'],
                'residential_state' => ['required', Rule::in($this->residence->restrictedToBesut() ? [config('besut.state')] : config('besut.states'))],
                'residential_district' => array_filter(['required', 'string', 'max:100', $this->residence->restrictedToBesut() ? Rule::in([config('besut.district')]) : null]),
                'residential_mukim' => array_filter([$isBesut ? 'required' : 'nullable', 'string', 'max:100', $isBesut ? Rule::in(config('besut.mukims')) : null]),
                'residential_village' => ['required', 'string', 'max:255'],
                'residential_postcode' => ['required', 'regex:/^\d{5}$/'],
                'education_level' => ['required', 'string', 'max:100'],
                'oku_card_number' => ['required', 'string', 'max:50', 'unique:okus,oku_card_number'],
                'oku_category' => ['required', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
                'employment_status' => ['required', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
                'job_name' => ['nullable', 'string', 'max:255'],
                'assistance_type' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $result['failed']++;
                $result['errors'][] = 'Baris '.$number.': '.$validator->errors()->first();

                continue;
            }

            Oku::query()->create($this->residence->declaration($validator->validated(), true));
            $result['imported']++;
        }

        return $result;
    }

    public function templateHeaders(): array
    {
        return array_keys(self::HEADERS);
    }

    private function normaliseData(array $data): array
    {
        $upper = fn (string $value) => mb_strtoupper(trim($value));

        $data['gender'] = match ($upper($data['gender'])) {
            'LELAKI' => 'Lelaki',
            'PEREMPUAN' => 'Perempuan',
            default => $data['gender'],
        };
        $data['marital_status'] = match ($upper($data['marital_status'])) {
            'BERKAHWIN' => 'Berkahwin',
            'BUJANG' => 'Bujang',
            'DUDA' => 'Duda',
            'JANDA', 'BALU' => 'Janda',
            default => $data['marital_status'],
        };
        $data['residential_mukim'] = collect(config('besut.mukims'))
            ->first(fn (string $mukim) => $upper($mukim) === $upper($data['residential_mukim']))
            ?? $data['residential_mukim'];
        $data['residential_state'] = collect(config('besut.states'))
            ->first(fn (string $state) => $upper($state) === $upper($data['residential_state']))
            ?? $data['residential_state'];
        if ($upper($data['residential_district']) === $upper(config('besut.district'))) {
            $data['residential_district'] = config('besut.district');
        }
        $data['oku_category'] = match ($upper($data['oku_category'])) {
            'FIZIKAL' => 'Fizikal',
            'PENDENGARAN' => 'Pendengaran',
            'MENTAL' => 'Mental',
            'PEMBELAJARAN' => 'Pembelajaran',
            'PENGLIHATAN' => 'Penglihatan',
            default => $data['oku_category'],
        };
        $data['employment_status'] = match ($upper($data['employment_status'])) {
            'TIDAK BEKERJA' => 'Tidak Bekerja',
            'SENDIRI', 'BEKERJA SENDIRI' => 'Sendiri',
            'BEKERJA', 'SWASTA', 'KERAJAAN' => 'Bekerja',
            default => $data['employment_status'],
        };
        $data['education_level'] = mb_convert_case($data['education_level'], MB_CASE_TITLE, 'UTF-8');
        $data['job_name'] = filled($data['job_name']) ? mb_convert_case($data['job_name'], MB_CASE_TITLE, 'UTF-8') : null;
        $data['assistance_type'] = filled($data['assistance_type']) ? $upper($data['assistance_type']) : null;
        $age = trim((string) $data['age']);
        if (preg_match('/^(\d{1,3})\s*(?:TAHUN)?$/iu', $age, $matches)) {
            $data['age'] = (int) $matches[1];
        }

        return $data;
    }

    private function normaliseHeader(string $header): string
    {
        return mb_strtoupper(trim(str_replace("\xEF\xBB\xBF", '', $header)));
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => trim((string) $value) === '');
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Fail CSV tidak dapat dibaca.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Fail XLSX tidak dapat dibuka.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = new SimpleXMLElement($sharedXml);
            foreach ($xml->si as $item) {
                $sharedStrings[] = $this->xlsxText($item);
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException('Helaian pertama XLSX tidak ditemui.');
        }

        $sheet = new SimpleXMLElement($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->columnIndex($match[0] ?? 'A');
                $type = (string) $cell['t'];
                $raw = (string) $cell->v;
                $value = match ($type) {
                    's' => $sharedStrings[(int) $raw] ?? '',
                    'inlineStr' => $this->xlsxText($cell->is),
                    default => $raw,
                };
                $values[$column] = $value;
            }
            if ($values !== []) {
                $max = max(array_keys($values));
                $rows[] = array_replace(array_fill(0, $max + 1, ''), $values);
            }
        }

        return $rows;
    }

    private function xlsxText(SimpleXMLElement $node): string
    {
        if (isset($node->t)) {
            return (string) $node->t;
        }

        $text = '';
        foreach ($node->r as $run) {
            $text .= (string) $run->t;
        }

        return $text;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }

        return $index - 1;
    }
}
