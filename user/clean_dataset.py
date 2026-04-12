import csv

def clean_skin_types(skin_type_str):
    replacements = {
        "dry skin": "dry",
        "normal skin": "normal",
        "oily skin": "oily",
        "combination skin": "combination"
    }
    types = [t.strip().lower() for t in skin_type_str.split(',')]
    cleaned = [replacements.get(t, t) for t in types]
    return ','.join(cleaned)

with open("ingredient_data.csv", newline='', encoding='utf-8') as infile, \
     open("ingredient_data_cleaned.csv", "w", newline='', encoding='utf-8') as outfile:

    reader = csv.DictReader(infile)
    fieldnames = reader.fieldnames
    writer = csv.DictWriter(outfile, fieldnames=fieldnames)
    writer.writeheader()

    for row in reader:
        row["Skin Type"] = clean_skin_types(row["Skin Type"])
        writer.writerow(row)

print("✅ Dataset cleaned and saved as ingredient_data_cleaned.csv")
