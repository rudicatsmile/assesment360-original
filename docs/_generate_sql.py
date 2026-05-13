import pandas as pd
import re

df = pd.read_excel(r'd:\Softwares\projects\mobile\yalwash9\assesment360-original\docs\DATA ORTU DP1.xlsx')

password_hash = '$2y$10$T5fnlIVC3dyJs72aNvWkSeI/sMUXd11EeYUNBipHm55iz/KzcYChm'

def name_to_email(name):
    base = name.lower().strip()
    base = re.sub(r'[^a-z\s]', '', base)
    base = re.sub(r'\s+', '.', base)
    return base

base_emails = df['NAMA'].apply(name_to_email)

email_counts = {}
final_emails = []
for e in base_emails:
    if e in email_counts:
        email_counts[e] += 1
        final_emails.append(f'{e}{email_counts[e]}@orangtua.dp1.sch.id')
    else:
        email_counts[e] = 1
        final_emails.append(f'{e}@orangtua.dp1.sch.id')

def format_phone(phone):
    p = str(int(float(phone)))
    if not p.startswith('0'):
        p = '0' + p
    return p

phones = df['NO HP'].apply(format_phone)
names = df['NAMA'].str.strip()

lines = []
lines.append('-- Generated from DATA ORTU DP1.xlsx')
lines.append(f'-- Total: {len(df)} users')
lines.append('-- Password: password (bcrypt hashed)')
lines.append('')

values = []
for i in range(len(df)):
    name = names.iloc[i].replace("'", "\\'")
    email = final_emails[i]
    phone = phones.iloc[i]
    vals = (
        f"('{name}', '{email}', '{phone}', "
        f"'{password_hash}', "
        f"'Orang Tua Murid/Wali Murid', 11, "
        f"'SMK Dinamika Pembangunan 1', 4, "
        f"15, NOW(), 1, NOW(), NOW())"
    )
    values.append(vals)

lines.append('INSERT INTO users (name, email, phone_number, password, role, role_id, department, department_id, time_limit_minutes, email_verified_at, is_active, created_at, updated_at) VALUES')
lines.append(',\n'.join(values) + ';')

sql = '\n'.join(lines)
output_path = r'd:\Softwares\projects\mobile\yalwash9\assesment360-original\docs\insert_orang_tua_dp1.sql'
with open(output_path, 'w', encoding='utf-8') as f:
    f.write(sql)

print(f'Done! Generated SQL file with {len(df)} INSERT records')
print(f'First email: {final_emails[0]}')
print(f'Last email: {final_emails[-1]}')
print(f'Sample phone: {phones.iloc[0]}')
print(f'Output: {output_path}')
