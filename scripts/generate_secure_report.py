import json, sys
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.cidfonts import UnicodeCIDFont

source, destination = sys.argv[1], sys.argv[2]
with open(source, encoding="utf-8") as handle:
    data = json.load(handle)

page_size = landscape(A4) if len(data["rows"][0]) > 6 else A4
doc = SimpleDocTemplate(destination, pagesize=page_size, leftMargin=16*mm, rightMargin=16*mm, topMargin=18*mm, bottomMargin=18*mm)
styles = getSampleStyleSheet()
body_font = "Helvetica"
heading_font = "Helvetica-Bold"
if data.get("language") == "ZH_CN":
    pdfmetrics.registerFont(UnicodeCIDFont("STSong-Light"))
    body_font = heading_font = "STSong-Light"
styles["Title"].fontName = heading_font
styles["Heading1"].fontName = heading_font
styles.add(ParagraphStyle(name="Meta", parent=styles["Normal"], fontName=body_font, fontSize=8, leading=11, textColor=colors.HexColor("#5B6472")))
styles.add(ParagraphStyle(name="TableHeader", parent=styles["Normal"], fontSize=8, leading=11, textColor=colors.white, fontName=heading_font))
story = [
    Paragraph("MyOKUcare", styles["Title"]),
    Paragraph(data["title"].title(), styles["Heading1"]),
    Paragraph(f"{data['labels']['reference']}: {data['reference']}<br/>{data['labels']['generatedBy']}: {data['role']}<br/>{data['labels']['organisation']}: {data['organisation']}<br/>{data['labels']['generatedAt']}: {data['generatedAt']}", styles["Meta"]),
    Spacer(1, 5*mm),
]
filters = ", ".join(f"{key}: {value}" for key, value in data.get("filters", {}).items() if value)
story.append(Paragraph(f"{data['labels']['appliedFilters']}: {filters or data['labels']['none']}", styles["Meta"]))
story.append(Paragraph(f"{data['labels']['recordSummary']}: {max(0, len(data['rows'])-1)} {data['labels']['records']}", styles["Meta"]))
story.append(Spacer(1, 5*mm))
table_data = [
    [Paragraph(str(cell), styles["TableHeader"] if row_index == 0 else styles["Meta"]) for cell in row]
    for row_index, row in enumerate(data["rows"])
]
available_width = page_size[0] - 32*mm
column_width = available_width / max(1, len(table_data[0]))
table = Table(table_data, colWidths=[column_width]*len(table_data[0]), repeatRows=1)
table.setStyle(TableStyle([
    ("BACKGROUND", (0,0), (-1,0), colors.HexColor("#B63D52")),
    ("TEXTCOLOR", (0,0), (-1,0), colors.white),
    ("FONTNAME", (0,0), (-1,0), heading_font),
    ("VALIGN", (0,0), (-1,-1), "TOP"),
    ("ROWBACKGROUNDS", (0,1), (-1,-1), [colors.white, colors.HexColor("#F7F8FA")]),
    ("LINEBELOW", (0,0), (-1,-1), 0.25, colors.HexColor("#D8DDE5")),
    ("LEFTPADDING", (0,0), (-1,-1), 5), ("RIGHTPADDING", (0,0), (-1,-1), 5),
    ("TOPPADDING", (0,0), (-1,-1), 5), ("BOTTOMPADDING", (0,0), (-1,-1), 5),
]))
story.append(table)

def footer(canvas, document):
    canvas.saveState()
    width, height = page_size
    canvas.setFillColor(colors.Color(0.72, 0.1, 0.18, alpha=0.08))
    canvas.setFont(heading_font, 26)
    canvas.translate(width/2, height/2)
    canvas.rotate(28)
    canvas.drawCentredString(0, 0, data["labels"]["confidential"])
    canvas.rotate(-28)
    canvas.translate(-width/2, -height/2)
    canvas.setFillColor(colors.HexColor("#606875"))
    canvas.setFont(body_font, 7)
    canvas.drawString(16*mm, 10*mm, data["labels"]["confidential"])
    canvas.drawRightString(width-16*mm, 10*mm, f"{data['labels']['page']} {document.page}")
    canvas.restoreState()

doc.build(story, onFirstPage=footer, onLaterPages=footer)
