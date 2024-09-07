<?php

namespace App\Service;

class CityService
{
    // Simulated function to fetch cities for a governorate (Replace with your logic)
    public function getCitiesForGovernorate(?string $governorate): array
    {

          // Check if the governorate value is null
    if ($governorate === null) {
        // Return an empty array if no governorate is selected
        return [];
    }
        // Simulated data, replace with actual data from your database
        $cities = [];
        

        // Add logic here to fetch cities based on the selected governorate
        switch ($governorate) {
            case 'Ariana':
                $cities = ['Ariana', 'Ettadhamen-Mnihla', 'Raoued', 'Sidi Thabet'];
                break;
            case 'Beja':
                $cities = ['Béja', 'Amdoun', 'Goubellat', 'Medjez el-Bab'];
                break;
            case 'Ben Arous':
                $cities = ['Ben Arous', 'Bou Mhel el-Bassatine', 'El Mourouj', 'Hammam Lif'];
                break;
            case 'Bizerte':
                $cities = ['Bizerte', 'Ghar El Melh', 'Menzel Bourguiba', 'Ras Jebel'];
                break;
            case 'Gabes':
                $cities = ['Gabès', 'Ghannouch', 'Mareth', 'Matmata'];
                break;
            case 'Gafsa':
                $cities = ['Gafsa', 'El Ksar', 'Mdhilla', 'Métlaoui'];
                break;
            case 'Jendouba':
                $cities = ['Jendouba', 'Bou Salem', 'Tabarka', 'Aïn Draham'];
                break;
            case 'Kairouan':
                $cities = ['Kairouan', 'El Alâa', 'Hajeb El Ayoun', 'Oueslatia'];
                break;
            case 'Kasserine':
                $cities = ['Kasserine', 'Sbeitla', 'Fériana', 'Sbiba'];
                break;
            case 'Kebili':
                $cities = ['Kebili', 'Douz', 'Souk Lahad', 'Faouar'];
                break;
            case 'Kef':
                $cities = ['Le Kef', 'Dahmani', 'Jérissa', 'Tajerouine'];
                break;
            case 'Mahdia':
                $cities = ['Mahdia', 'Bou Merdes', 'Chebba', 'El Jem'];
                break;
            case 'Manouba':
                $cities = ['Manouba', 'Borj El Amri', 'Djedeida', 'Douar Hicher'];
                break;
            case 'Medenine':
                $cities = ['Médenine', 'Ben Gardane', 'Djerba', 'Zarzis'];
                break;
            case 'Monastir':
                $cities = ['Monastir', 'Moknine', 'Bembla', 'Jemmal'];
                break;
            case 'Nabeul':
                $cities = ['Nabeul', 'Dar Chaabane', 'El Maâmoura', 'Hammam Ghezèze'];
                break;
            case 'Sfax':
                $cities = ['Sfax', 'Agareb', 'El Hencha', 'Gremda'];
                break;
            case 'Sidi Bouzid':
                $cities = ['Sidi Bouzid', 'Jilma', 'Meknassy', 'Regueb'];
                break;
            case 'Siliana':
                $cities = ['Siliana', 'Bou Arada', 'El Krib', 'Gaâfour'];
                break;
            case 'Sousse':
                $cities = ['Sousse', 'Akouda', 'Enfidha', 'Hergla'];
                break;
            case 'Tataouine':
                $cities = ['Tataouine', 'Bir Lahmar', 'Dehiba', 'Ghomrassen'];
                break;
            case 'Tozeur':
                $cities = ['Tozeur', 'Degache', 'Hazoua', 'Nefta'];
                break;
            case 'Tunis':
                $cities = ['Tunis', 'Carthage', 'La Marsa', 'Sidi Bou Said'];
                break;
            case 'Zaghouan':
                $cities = ['Zaghouan', 'Fahs', 'Nadhour', 'Zriba'];
                break;
            // Add more cases for other governorates
            default:
                // Return an empty array if no governorate is selected
                break;
        }

        return $cities;
    }
}
