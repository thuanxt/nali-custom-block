#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Convert PHP address data to JavaScript format
"""

import json
import re

# Read PHP files
with open('tinh_thanhpho.php', 'r', encoding='utf-8') as f:
    tinh_content = f.read()

with open('quan_huyen.php', 'r', encoding='utf-8') as f:
    quan_content = f.read()

with open('xa_phuong_thitran.php', 'r', encoding='utf-8') as f:
    xa_content = f.read()

# Parse provinces
provinces = {}
province_pattern = r'"([A-Z_]+)"\s*=>\s*"([^"]+)"'
for match in re.finditer(province_pattern, tinh_content):
    code, name = match.groups()
    if code:  # Skip empty codes
        provinces[code] = {'name': name, 'districts': {}}

# Parse districts
district_pattern = r'"maqh"=>"(\d+)","name"=>"([^"]+)","matp"=>"([A-Z_]+)"'
districts = {}
for match in re.finditer(district_pattern, quan_content):
    maqh, name, matp = match.groups()
    if matp in provinces:
        districts[maqh] = {
            'name': name,
            'province_code': matp,
            'wards': {}
        }

# Parse wards
ward_pattern = r'"xaid"=>"(\d+)","name"=>"([^"]+)","maqh"=>"(\d+)"'
for match in re.finditer(ward_pattern, xa_content):
    xaid, name, maqh = match.groups()
    if maqh in districts:
        districts[maqh]['wards'][xaid] = name

# Build final structure
for maqh, district_data in districts.items():
    province_code = district_data['province_code']
    if province_code in provinces:
        # Use district code as key
        district_key = 'D_' + maqh
        provinces[province_code]['districts'][district_key] = {
            'name': district_data['name'],
            'wards': district_data['wards'],
            'postcode': ''  # You can add postcode mapping if needed
        }

# Generate JavaScript code
js_code = "/**\n * Vietnam Address Data - Complete 63 provinces\n * Auto-generated from PHP data\n */\n\n"
js_code += "const vietnamAddressData = " + json.dumps(provinces, ensure_ascii=False, indent='\t') + ";\n\n"
js_code += "// Export for use in view.js\n"
js_code += "if (typeof module !== 'undefined' && module.exports) {\n"
js_code += "\tmodule.exports = vietnamAddressData;\n"
js_code += "}\n"

# Write to file
with open('../profile-editor-block/vietnam-address-data.js', 'w', encoding='utf-8') as f:
    f.write(js_code)

print("✅ Conversion completed!")
print(f"📊 Total provinces: {len(provinces)}")
print(f"📊 Total districts: {len(districts)}")
print(f"✨ File created: ../profile-editor-block/vietnam-address-data.js")
