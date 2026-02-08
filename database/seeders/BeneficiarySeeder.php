<?php

namespace Database\Seeders;

use App\Models\Beneficiary;
use Illuminate\Database\Seeder;

class BeneficiarySeeder extends Seeder
{
    public function run()
    {
        // Clear existing beneficiaries (delete instead of truncate to avoid foreign key issues)
        Beneficiary::query()->delete();
        
        $beneficiaries = [
            ['id' => 1, 'name' => 'RADHESHYAM SHAH', 'acode' => '0.1.01.01.01', 'vendor_code' => 'V1830100033287', 'salary' => 0, 'status' => 1],
            ['id' => 2, 'name' => 'RADHESHYAM SHAH', 'acode' => '0.1.01.01.02', 'vendor_code' => 'V1830100036004', 'salary' => 0, 'status' => 1],
            ['id' => 3, 'name' => 'GOPAL TANTI', 'acode' => '0.1.01.01.03', 'vendor_code' => 'V1830100035999', 'salary' => 0, 'status' => 1],
            ['id' => 4, 'name' => 'MD JAMIR ALI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100036007', 'salary' => 0, 'status' => 1],
            ['id' => 5, 'name' => 'MISS ABEDA KHATUN', 'acode' => '0.1.01.01.05', 'vendor_code' => 'V1830100036006', 'salary' => 0, 'status' => 1],
            ['id' => 6, 'name' => 'NARAYAN CH SAHA', 'acode' => '0.1.01.01.06', 'vendor_code' => 'V1830100035950', 'salary' => 0, 'status' => 1],
            ['id' => 7, 'name' => 'PROMOD STORE', 'acode' => '0.1.01.01.07', 'vendor_code' => 'V1830100035646', 'salary' => 0, 'status' => 1],
            ['id' => 8, 'name' => 'PARBATI STORE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100035647', 'salary' => 0, 'status' => 1],
            ['id' => 9, 'name' => 'ASKARAN HIRALAL TEXTILES PRIVATE LIMITED', 'acode' => '0.1.01.01.09', 'vendor_code' => 'V1830100036005', 'salary' => 0, 'status' => 1],
            ['id' => 10, 'name' => 'APDCL REVENUE DEPOSIT AC', 'acode' => '0.1.01.01.10', 'vendor_code' => 'V1830100036635', 'salary' => 0, 'status' => 1],
            ['id' => 11, 'name' => 'GOPAL TANTI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100036004', 'salary' => 0, 'status' => 1],
            ['id' => 12, 'name' => 'PROMOD STORE', 'acode' => '0.1.01.01.12', 'vendor_code' => 'V1830100033288', 'salary' => 0, 'status' => 1],
            ['id' => 13, 'name' => 'ASKARAN HIRALAL TEXTILES PRIVATE LIMITED', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033289', 'salary' => 0, 'status' => 1],
            ['id' => 14, 'name' => 'ANINDITA STATIONERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033290', 'salary' => 0, 'status' => 1],
            ['id' => 15, 'name' => 'Pallabi Sarmah', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033291', 'salary' => 0, 'status' => 1],
            ['id' => 16, 'name' => 'SHANTI TRADERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033292', 'salary' => 0, 'status' => 1],
            ['id' => 17, 'name' => 'SUSHAMA STEEL FURNITURE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033293', 'salary' => 0, 'status' => 1],
            ['id' => 18, 'name' => 'GUPTA COMPUTERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033294', 'salary' => 0, 'status' => 1],
            ['id' => 19, 'name' => 'PARTHA PRATIM DEVROY', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1829800032557', 'salary' => 0, 'status' => 1],
            ['id' => 20, 'name' => 'Navin Agarwal', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100090894', 'salary' => 0, 'status' => 1],
            ['id' => 21, 'name' => 'Chandra Kamal Deka', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1828600049022', 'salary' => 0, 'status' => 1],
            ['id' => 22, 'name' => 'Juli Mhanta', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1828000110849', 'salary' => 0, 'status' => 1],
            ['id' => 23, 'name' => 'Mr Rupom Singh Gour', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100090908', 'salary' => 0, 'status' => 1],
            ['id' => 24, 'name' => 'Sunny Paul Kullu', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100090907', 'salary' => 0, 'status' => 1],
            ['id' => 25, 'name' => 'DULAL CHANDRA DAS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033547', 'salary' => 0, 'status' => 1],
            ['id' => 26, 'name' => 'PANKAJ PRATIM BORA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100016428', 'salary' => 0, 'status' => 1],
            ['id' => 27, 'name' => 'ANJAN KR DEKA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100016004', 'salary' => 0, 'status' => 1],
            ['id' => 28, 'name' => 'KHIRADA DEKA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100091564', 'salary' => 0, 'status' => 1],
            ['id' => 29, 'name' => 'MITALI DOLOI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100093934', 'salary' => 0, 'status' => 1],
            ['id' => 30, 'name' => 'RIMJIM SAIKIA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100093940', 'salary' => 0, 'status' => 1],
            ['id' => 31, 'name' => 'KB TILES AND HARDWARE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100064762', 'salary' => 0, 'status' => 1],
            ['id' => 32, 'name' => 'JAMIR ALI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100096107', 'salary' => 0, 'status' => 1],
            ['id' => 33, 'name' => 'KGBV RESIDENTIAL SCHOOL DHEKIAJULI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100036117', 'salary' => 0, 'status' => 1],
            ['id' => 34, 'name' => 'JAIN STATIONERY', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100065917', 'salary' => 0, 'status' => 1],
            ['id' => 35, 'name' => 'RUBI NATH', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099735', 'salary' => 0, 'status' => 1],
            ['id' => 36, 'name' => 'MITU BISWAS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099736', 'salary' => 0, 'status' => 1],
            ['id' => 37, 'name' => 'SASHINDRA DAS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099764', 'salary' => 0, 'status' => 1],
            ['id' => 38, 'name' => 'LK ENTERPRISES', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100068925', 'salary' => 0, 'status' => 1],
            ['id' => 39, 'name' => 'ANYA GUPTA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100100119', 'salary' => 0, 'status' => 1],
            ['id' => 40, 'name' => 'LAMA HOTEL', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100100510', 'salary' => 0, 'status' => 1],
            ['id' => 41, 'name' => 'SHIKHAMONI KALITA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100101556', 'salary' => 0, 'status' => 1],
            ['id' => 42, 'name' => 'GLOBAL OFFSET', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100059836', 'salary' => 0, 'status' => 1],
            ['id' => 43, 'name' => 'MOUSUMI TRADERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100091662', 'salary' => 0, 'status' => 1],
            ['id' => 44, 'name' => 'MAA SARADA TRADING', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100069111', 'salary' => 0, 'status' => 1],
            ['id' => 45, 'name' => 'Runjun Kalita', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100095094', 'salary' => 0, 'status' => 1],
            ['id' => 46, 'name' => 'Rashmi Rekha Sarmah', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100094939', 'salary' => 0, 'status' => 1],
            ['id' => 47, 'name' => 'Sewali Goswami', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100095096', 'salary' => 0, 'status' => 1],
            ['id' => 48, 'name' => 'Manmayuri Goswami', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100095061', 'salary' => 0, 'status' => 1],
            ['id' => 49, 'name' => 'MONALICHA P CHETIA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100095093', 'salary' => 0, 'status' => 1],
            ['id' => 50, 'name' => 'NEELU KUMARI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100065227', 'salary' => 0, 'status' => 1],
            ['id' => 51, 'name' => 'RAKESH KUMAR', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100107237', 'salary' => 0, 'status' => 1],
            ['id' => 52, 'name' => 'BIKI STORE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100112367', 'salary' => 0, 'status' => 1],
            ['id' => 53, 'name' => 'Rumi Sakia', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033291', 'salary' => 0, 'status' => 1],
            ['id' => 54, 'name' => 'KABITA DEVI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1870500009869', 'salary' => 0, 'status' => 1],
            ['id' => 55, 'name' => 'Ili saikia Neog', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033290', 'salary' => 0, 'status' => 1],
            ['id' => 56, 'name' => 'Kalpana Barhoi', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033289', 'salary' => 0, 'status' => 1],
            ['id' => 57, 'name' => 'Mitali Doloi', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100093934', 'salary' => 0, 'status' => 1],
            ['id' => 58, 'name' => 'Rimjim Saikia', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100093940', 'salary' => 0, 'status' => 1],
            ['id' => 59, 'name' => 'Pallabi Sarmah', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100060060', 'salary' => 0, 'status' => 1],
            ['id' => 60, 'name' => 'Aklima Ahmed', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100060059', 'salary' => 0, 'status' => 1],
            ['id' => 61, 'name' => 'Tridisha Bhatta', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100058078', 'salary' => 0, 'status' => 1],
            ['id' => 62, 'name' => 'Dipsikha Kalita', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100060069', 'salary' => 0, 'status' => 1],
            ['id' => 63, 'name' => 'RUBI NATH', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099735', 'salary' => 0, 'status' => 1],
            ['id' => 64, 'name' => 'JYOTI BORO', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100092018', 'salary' => 0, 'status' => 1],
            ['id' => 65, 'name' => 'PAPORI CHUTIA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100111773', 'salary' => 0, 'status' => 1],
            ['id' => 66, 'name' => 'PORISHMITA BORA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100111772', 'salary' => 0, 'status' => 1],
            ['id' => 67, 'name' => 'Monika Devi', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100090724', 'salary' => 0, 'status' => 1],
            ['id' => 68, 'name' => 'MITU BISWAS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099736', 'salary' => 0, 'status' => 1],
            ['id' => 69, 'name' => 'SHIKHAMONI KALITA', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100101556', 'salary' => 0, 'status' => 1],
            ['id' => 70, 'name' => 'Radheshyam Shah', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033287', 'salary' => 0, 'status' => 1],
            ['id' => 71, 'name' => 'Biplab Dutta', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033286', 'salary' => 0, 'status' => 1],
            ['id' => 72, 'name' => 'Jonali Swargary', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033285', 'salary' => 0, 'status' => 1],
            ['id' => 73, 'name' => 'Sashindra Das', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100099764', 'salary' => 0, 'status' => 1],
            ['id' => 74, 'name' => 'Jyotirupa Deka', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033277', 'salary' => 0, 'status' => 1],
            ['id' => 75, 'name' => 'Lalmoni Mahato', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033265', 'salary' => 0, 'status' => 1],
            ['id' => 76, 'name' => 'Rumi Saikia', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033291', 'salary' => 0, 'status' => 1],
            ['id' => 77, 'name' => 'RADHESHYAM SHAH', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033287', 'salary' => 0, 'status' => 1],
            ['id' => 78, 'name' => 'GOPAL TANTI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100036004', 'salary' => 0, 'status' => 1],
            ['id' => 79, 'name' => 'PROMOD STORE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033288', 'salary' => 0, 'status' => 1],
            ['id' => 80, 'name' => 'ASKARAN HIRALAL TEXTILES PRIVATE LIMITED', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033289', 'salary' => 0, 'status' => 1],
            ['id' => 81, 'name' => 'ANINDITA STATIONERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033290', 'salary' => 0, 'status' => 1],
            ['id' => 82, 'name' => 'Pallabi Sarmah', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033291', 'salary' => 0, 'status' => 1],
            ['id' => 83, 'name' => 'SHANTI TRADERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033292', 'salary' => 0, 'status' => 1],
            ['id' => 84, 'name' => 'SUSHAMA STEEL FURNITURE', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033293', 'salary' => 0, 'status' => 1],
            ['id' => 85, 'name' => 'GUPTA COMPUTERS', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100033294', 'salary' => 0, 'status' => 1],
            ['id' => 86, 'name' => 'MD JAMIR ALI', 'acode' => '0.1.01.01.08', 'vendor_code' => 'V1830100036007', 'salary' => 0, 'status' => 1],
        ];

        foreach ($beneficiaries as $beneficiary) {
            Beneficiary::create([
                'name' => $beneficiary['name'],
                'acode' => $beneficiary['acode'],
                'vendor_code' => $beneficiary['vendor_code'],
                'salary' => $beneficiary['salary'],
                'status' => $beneficiary['status'],
            ]);
        }
    }
}
