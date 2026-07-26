import cv2
import json
import math
import sys
import numpy as np

source, destination = sys.argv[1], sys.argv[2]
image = cv2.imread(source)
if image is None:
    raise SystemExit("IMAGE_DECODE_FAILED")

height, width = image.shape[:2]
gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
brightness = float(np.mean(gray))
blur_variance = float(cv2.Laplacian(gray, cv2.CV_64F).var())
overexposed = float(np.mean(gray >= 245))
dark = float(np.mean(gray <= 20))

edges = cv2.Canny(cv2.GaussianBlur(gray, (5, 5), 0), 50, 150)
contours, _ = cv2.findContours(edges, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
card = None
for contour in sorted(contours, key=cv2.contourArea, reverse=True)[:20]:
    perimeter = cv2.arcLength(contour, True)
    polygon = cv2.approxPolyDP(contour, 0.02 * perimeter, True)
    if len(polygon) == 4 and cv2.contourArea(polygon) >= width * height * 0.20:
        card = polygon.reshape(4, 2).astype("float32")
        break

issues = []
if width < 800 or height < 500: issues.append("IMAGE_LOW_RESOLUTION")
if blur_variance < 75: issues.append("IMAGE_BLURRY")
if brightness < 55 or dark > 0.35: issues.append("IMAGE_TOO_DARK")
if brightness > 220 or overexposed > 0.28: issues.append("IMAGE_OVEREXPOSED")
if card is None:
    issues.append("CARD_NOT_DETECTED")
    issues.append("CARD_CORNER_MISSING")
    processed = image
    card_ratio = 0.0
else:
    card_ratio = cv2.contourArea(card) / (width * height)
    if card_ratio < 0.35: issues.append("CARD_TOO_SMALL")
    sums, diffs = card.sum(axis=1), np.diff(card, axis=1).reshape(-1)
    ordered = np.array([card[np.argmin(sums)], card[np.argmin(diffs)], card[np.argmax(sums)], card[np.argmax(diffs)]], dtype="float32")
    top_angle = abs(math.degrees(math.atan2(ordered[1][1] - ordered[0][1], ordered[1][0] - ordered[0][0])))
    if top_angle > 12: issues.append("CARD_TILTED")
    target_width = 1012
    target_height = 638
    target = np.array([[0, 0], [target_width - 1, 0], [target_width - 1, target_height - 1], [0, target_height - 1]], dtype="float32")
    matrix = cv2.getPerspectiveTransform(ordered, target)
    processed = cv2.warpPerspective(image, matrix, (target_width, target_height))

processed = cv2.fastNlMeansDenoisingColored(processed, None, 3, 3, 7, 21)
processed_gray = cv2.cvtColor(processed, cv2.COLOR_BGR2GRAY)
glare_mask = (processed_gray >= 250).astype(np.uint8)
glare_contours, _ = cv2.findContours(glare_mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
if any(cv2.contourArea(c) / processed_gray.size > 0.035 for c in glare_contours):
    issues.append("GLARE_DETECTED")
lab = cv2.cvtColor(processed, cv2.COLOR_BGR2LAB)
l, a, b = cv2.split(lab)
l = cv2.createCLAHE(clipLimit=1.5, tileGridSize=(8, 8)).apply(l)
processed = cv2.cvtColor(cv2.merge((l, a, b)), cv2.COLOR_LAB2BGR)
cv2.imwrite(destination, processed, [cv2.IMWRITE_JPEG_QUALITY, 92])

penalty = min(1.0, len(issues) * 0.18)
quality_score = round(max(0.0, 1.0 - penalty), 4)
print(json.dumps({
    "width": width, "height": height, "brightness": round(brightness, 2),
    "blurVariance": round(blur_variance, 2), "overexposedRatio": round(overexposed, 4),
    "cardAreaRatio": round(card_ratio, 4), "cornersDetected": card is not None,
    "qualityScore": quality_score, "issues": issues,
}))
