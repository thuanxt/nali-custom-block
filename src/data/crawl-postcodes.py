#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to crawl postal codes from mabuudien.net for all Vietnamese provinces
"""

import requests
from bs4 import BeautifulSoup
import json
import time
import re
from urllib.parse import quote

# Mapping province codes to Vietnamese names (from tinh_thanhpho.php)
PROVINCES = {
    "ANGIANG": "An Giang",
    "BARIA": "Bà Rịa - Vũng Tàu",
    "BACLIEU": "Bạc Liêu",
    "BACGIANG": "Bắc Giang",
    "BACKAN": "Bắc Kạn",
    "BACNINH": "Bắc Ninh",
    "BENTRE": "Bến Tre",
    "BINHDINH": "Bình Định",
    "BINHDUONG": "Bình Dương",
    "BINHPHUOC": "Bình Phước",
    "BINHTHUAN": "Bình Thuận",
    "CAMAU": "Cà Mau",
    "CAOBANG": "Cao Bằng",
    "CANTHO": "Cần Thơ",
    "DANANG": "Đà Nẵng",
    "DAKLAK": "Đắk Lắk",
    "DAKNONG": "Đắk Nông",
    "DIENBIEN": "Điện Biên",
    "DONGNAI": "Đồng Nai",
    "DONGTHAP": "Đồng Tháp",
    "GIALAI": "Gia Lai",
    "HAGIANG": "Hà Giang",
    "HANAM": "Hà Nam",
    "HANOI": "Hà Nội",
    "HATINH": "Hà Tĩnh",
    "HAIDUONG": "Hải Dương",
    "HAIPHONG": "Hải Phòng",
    "HAUGIANG": "Hậu Giang",
    "HOABINH": "Hòa Bình",
    "HOCHIMINH": "Hồ Chí Minh",
    "HUNGYEN": "Hưng Yên",
    "KHANHHOA": "Khánh Hòa",
    "KIENGIANG": "Kiên Giang",
    "KONTUM": "Kon Tum",
    "LAICHAU": "Lai Châu",
    "LAMDONG": "Lâm Đồng",
    "LANGSON": "Lạng Sơn",
    "LAOCAI": "Lào Cai",
    "LONGAN": "Long An",
    "NAMDINH": "Nam Định",
    "NGHEAN": "Nghệ An",
    "NINHBINH": "Ninh Bình",
    "NINHTHUAN": "Ninh Thuận",
    "PHUTHO": "Phú Thọ",
    "PHUYEN": "Phú Yên",
    "QUANGBINH": "Quảng Bình",
    "QUANGNAM": "Quảng Nam",
    "QUANGNGAI": "Quảng Ngãi",
    "QUANGNINH": "Quảng Ninh",
    "QUANGTRI": "Quảng Trị",
    "SOCTRANG": "Sóc Trăng",
    "SONLA": "Sơn La",
    "TAYNINH": "Tây Ninh",
    "THAIBINH": "Thái Bình",
    "THAINGUYEN": "Thái Nguyên",
    "THANHHOA": "Thanh Hóa",
    "THUATHIENHUE": "Thừa Thiên Huế",
    "TIENGIANG": "Tiền Giang",
    "TRAVINH": "Trà Vinh",
    "TUYENQUANG": "Tuyên Quang",
    "VINHLONG": "Vĩnh Long",
    "VINHPHUC": "Vĩnh Phúc",
    "YENBAI": "Yên Bái"
}

def slugify_vietnamese(text):
    """Convert Vietnamese text to URL-friendly slug"""
    # Lowercase
    text = text.lower()
    
    # Replace Vietnamese characters
    replacements = {
        'á': 'a', 'à': 'a', 'ả': 'a', 'ã': 'a', 'ạ': 'a',
        'ă': 'a', 'ắ': 'a', 'ằ': 'a', 'ẳ': 'a', 'ẵ': 'a', 'ặ': 'a',
        'â': 'a', 'ấ': 'a', 'ầ': 'a', 'ẩ': 'a', 'ẫ': 'a', 'ậ': 'a',
        'é': 'e', 'è': 'e', 'ẻ': 'e', 'ẽ': 'e', 'ẹ': 'e',
        'ê': 'e', 'ế': 'e', 'ề': 'e', 'ể': 'e', 'ễ': 'e', 'ệ': 'e',
        'í': 'i', 'ì': 'i', 'ỉ': 'i', 'ĩ': 'i', 'ị': 'i',
        'ó': 'o', 'ò': 'o', 'ỏ': 'o', 'õ': 'o', 'ọ': 'o',
        'ô': 'o', 'ố': 'o', 'ồ': 'o', 'ổ': 'o', 'ỗ': 'o', 'ộ': 'o',
        'ơ': 'o', 'ớ': 'o', 'ờ': 'o', 'ở': 'o', 'ỡ': 'o', 'ợ': 'o',
        'ú': 'u', 'ù': 'u', 'ủ': 'u', 'ũ': 'u', 'ụ': 'u',
        'ư': 'u', 'ứ': 'u', 'ừ': 'u', 'ử': 'u', 'ữ': 'u', 'ự': 'u',
        'ý': 'y', 'ỳ': 'y', 'ỷ': 'y', 'ỹ': 'y', 'ỵ': 'y',
        'đ': 'd',
        ' ': '-', '/': '-'
    }
    
    for vn_char, en_char in replacements.items():
        text = text.replace(vn_char, en_char)
    
    # Remove special characters
    text = re.sub(r'[^a-z0-9\-]', '', text)
    
    # Remove multiple hyphens
    text = re.sub(r'-+', '-', text)
    
    return text.strip('-')

def crawl_province_postcodes(province_name):
    """Crawl postal codes for a specific province"""
    province_slug = slugify_vietnamese(province_name)
    url = f"https://mabuudien.net/ma-buu-dien-{province_slug}"
    
    print(f"📍 Crawling {province_name}: {url}")
    
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        response.encoding = 'utf-8'
        
        if response.status_code != 200:
            print(f"   ❌ Error: HTTP {response.status_code}")
            return {}
        
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Find the table with district postal codes
        districts = {}
        tables = soup.find_all('table')
        
        for table in tables:
            rows = table.find_all('tr')
            for row in rows:
                cols = row.find_all('td')
                if len(cols) >= 2:
                    district_name = cols[0].get_text(strip=True)
                    postcode = cols[1].get_text(strip=True)
                    
                    # Clean district name (remove "Quận", "Huyện", "Thị xã", "Thành phố")
                    district_clean = district_name
                    for prefix in ['Quận ', 'Huyện ', 'Thị xã ', 'Thành phố ']:
                        if district_clean.startswith(prefix):
                            district_clean = district_clean[len(prefix):]
                    
                    if postcode and postcode.isdigit() and len(postcode) == 5:
                        districts[district_clean] = postcode
        
        print(f"   ✅ Found {len(districts)} districts")
        return districts
        
    except Exception as e:
        print(f"   ❌ Error: {str(e)}")
        return {}

def load_districts_mapping():
    """Load district mapping from quan_huyen.php"""
    districts_map = {}
    
    with open('../data/quan_huyen.php', 'r', encoding='utf-8') as f:
        content = f.read()
        
        # Find district entries
        pattern = r'array\("maqh"=>"([^"]+)","name"=>"([^"]+)","matp"=>"([^"]+)"\)'
        matches = re.findall(pattern, content)
        
        for district_code, district_name, province_code in matches:
            # Clean district name
            district_clean = district_name
            for prefix in ['Quận ', 'Huyện ', 'Thị xã ', 'Thành phố ']:
                if district_clean.startswith(prefix):
                    district_clean = district_clean[len(prefix):]
            
            key = f"{province_code}_{district_code}"
            districts_map[key] = {
                'code': district_code,
                'name': district_name,
                'clean_name': district_clean,
                'province': province_code
            }
    
    print(f"📊 Loaded {len(districts_map)} districts from PHP file")
    return districts_map

def normalize_district_name(name):
    """Normalize district name for matching"""
    # Remove accents and special chars
    normalized = slugify_vietnamese(name)
    # Remove common prefixes
    for prefix in ['quan-', 'huyen-', 'thi-xa-', 'thanh-pho-']:
        if normalized.startswith(prefix):
            normalized = normalized[len(prefix):]
    return normalized

def main():
    print("🚀 Starting postal code crawler...")
    print("=" * 60)
    
    # Load districts mapping
    districts_map = load_districts_mapping()
    
    # Crawl postcodes for each province
    all_postcodes = {}
    
    for province_code, province_name in PROVINCES.items():
        postcodes = crawl_province_postcodes(province_name)
        
        if postcodes:
            all_postcodes[province_code] = postcodes
            time.sleep(1)  # Be nice to the server
    
    print("\n" + "=" * 60)
    print("📊 SUMMARY")
    print("=" * 60)
    print(f"Total provinces crawled: {len(all_postcodes)}")
    
    # Match postcodes with district codes
    matched_postcodes = {}
    unmatched = []
    
    for province_code, postcodes in all_postcodes.items():
        for district_name, postcode in postcodes.items():
            # Try to find matching district code
            matched = False
            normalized_name = normalize_district_name(district_name)
            
            for key, district_info in districts_map.items():
                if district_info['province'] == province_code:
                    district_normalized = normalize_district_name(district_info['clean_name'])
                    
                    if normalized_name == district_normalized:
                        matched_key = f"{province_code}_D_{district_info['code']}"
                        matched_postcodes[matched_key] = postcode
                        matched = True
                        break
            
            if not matched:
                unmatched.append(f"{province_code}: {district_name} = {postcode}")
    
    print(f"Total postcodes matched: {len(matched_postcodes)}")
    print(f"Unmatched: {len(unmatched)}")
    
    if unmatched:
        print("\n⚠️  Unmatched districts:")
        for item in unmatched[:20]:  # Show first 20
            print(f"   {item}")
    
    # Save to JSON file
    output_file = 'postcodes-mapping.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(matched_postcodes, f, ensure_ascii=False, indent=2)
    
    print(f"\n✅ Saved to {output_file}")
    print("=" * 60)

if __name__ == "__main__":
    main()
