#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple script to update postcodes using sed-style replacement
"""

import json
import subprocess

def main():
    print("🚀 Starting postcode update...")
    print("=" * 60)
    
    # Load postcodes mapping
    with open('postcodes-mapping.json', 'r', encoding='utf-8') as f:
        postcodes = json.load(f)
    
    print(f"📊 Loaded {len(postcodes)} postcodes")
    
    # Read the file
    js_file = '../profile-editor-block/vietnam-address-data.js'
    with open(js_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace postcodes
    updates = 0
    
    for key, postcode in postcodes.items():
        # Parse key: "HANOI_D_001"
        parts = key.split('_')
        if len(parts) < 3:
            continue
        
        province = parts[0]
        district = '_'.join(parts[1:])  # D_001
        
        # Find the district block and update its postcode
        # Look for pattern like: "D_001": { ... "postcode": "" }
        
        # Split content by district blocks
        district_marker = f'"{district}":'
        if district_marker in content:
            # Find all occurrences
            start_idx = 0
            while True:
                idx = content.find(district_marker, start_idx)
                if idx == -1:
                    break
                
                # Find the postcode line after this district
                postcode_idx = content.find('"postcode":', idx)
                if postcode_idx != -1 and postcode_idx < idx + 2000:  # Within reasonable distance
                    # Find the value
                    value_start = content.find('""', postcode_idx)
                    if value_start != -1 and value_start < postcode_idx + 50:
                        # Check if it's empty
                        if content[value_start:value_start+2] == '""':
                            # Replace it
                            content = content[:value_start] + f'"{postcode}"' + content[value_start+2:]
                            updates += 1
                            if updates % 50 == 0:
                                print(f"   Updated {updates} postcodes...")
                            break
                
                start_idx = idx + 1
    
    # Write back
    with open(js_file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"\n✅ Total updates: {updates}")
    print(f"✅ Updated {js_file}")
    print("=" * 60)

if __name__ == "__main__":
    main()
