import sys
import pytesseract
from PIL import Image

if len(sys.argv) < 2:
    print("No image path provided.")
    sys.exit(1)

image_path = sys.argv[1]
text = pytesseract.image_to_string(Image.open(image_path))

print("Raw extracted text:")
print(text)
# You can add your matching logic here and print results
