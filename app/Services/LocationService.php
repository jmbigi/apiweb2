<?php

namespace App\Services;

use Stevebauman\Location\Facades\Location;
use Illuminate\Http\Request;

class LocationService
{

    public const LANGUAGE_BY_COUNTRY = [
        'CN' => 'zh', // China - 1.412 millones - Chino mandarín
        'IN' => 'hi', // India - 1.366 millones - Hindi
        'US' => 'en', // Estados Unidos - 332 millones - Inglés
        'ID' => 'id', // Indonesia - 275 millones - Indonesio
        'BR' => 'pt', // Brasil - 213 millones - Portugués
        'PK' => 'ur', // Pakistán - 240 millones - Urdu
        'NG' => 'en', // Nigeria - 223 millones - Inglés
        'JP' => 'ja', // Japón - 125 millones - Japonés
        'ET' => 'am', // Etiopía - 126 millones - Amárico
        'MX' => 'es', // México - 126 millones - Español
        'PH' => 'tl', // Filipinas - 113 millones - Filipino
        'EG' => 'ar', // Egipto - 110 millones - Árabe
        'RU' => 'ru', // Rusia - 145 millones - Ruso
        'FR' => 'fr', // Francia - 67 millones - Francés
        'GB' => 'en', // Reino Unido - 67 millones - Inglés
        'TZ' => 'sw', // Tanzania - 67 millones - Suajili
        'TH' => 'th', // Tailandia - 71 millones - Tailandés
        'DE' => 'de', // Alemania - 83 millones - Alemán
        'IT' => 'it', // Italia - 60 millones - Italiano
        'ZA' => 'en', // Sudáfrica - 60 millones - Inglés
        'KR' => 'ko', // Corea del Sur - 52 millones - Coreano
        'CO' => 'es', // Colombia - 54 millones - Español
        'MM' => 'my', // Birmania (Myanmar) - 55 millones - Birmano
        'KE' => 'sw', // Kenia - 55 millones - Suajili
        'ES' => 'es', // España - 47 millones - Español
        'AR' => 'es', // Argentina - 45 millones - Español
        'IQ' => 'ar', // Irak - 44 millones - Árabe
        'DZ' => 'ar', // Argelia - 45 millones - Árabe
        'VN' => 'vi', // Vietnam - 99 millones - Vietnamita
        'BD' => 'bn', // Bangladesh - 171 millones - Bengalí
        'UA' => 'uk', // Ucrania - 42 millones - Ucraniano
        'SY' => 'ar', // Siria - 18 millones - Árabe
        'KH' => 'km', // Camboya - 16 millones - Jemer
        'SO' => 'so', // Somalia - 17 millones - Somali
        'RW' => 'rw', // Ruanda - 13 millones - Kinyarwanda
        'BI' => 'fr', // Burundi - 12 millones - Francés
        'DO' => 'es', // República Dominicana - 11 millones - Español
        'HT' => 'ht', // Haití - 12 millones - Criollo haitiano
        'JO' => 'ar', // Jordania - 11 millones - Árabe
        'LB' => 'ar', // Líbano - 6 millones - Árabe
        'LY' => 'ar', // Libia - 7 millones - Árabe
        'TM' => 'tk', // Turkmenistán - 6 millones - Turcomano
        'SV' => 'es', // El Salvador - 6 millones - Español
        'OM' => 'ar', // Omán - 5 millones - Árabe
        'AE' => 'ar', // Emiratos Árabes Unidos - 9 millones - Árabe
        'TG' => 'fr', // Togo - 9 millones - Francés
        'YE' => 'ar', // Yemen - 30 millones - Árabe
        'QA' => 'ar', // Catar - 3 millones - Árabe
        'BH' => 'ar', // Baréin - 2 millones - Árabe
        'MV' => 'dv', // Maldivas - 0.5 millones - Maldivo
        'GN' => 'fr', // Guinea - 14 millones - Francés
        'GM' => 'en', // Gambia - 2 millones - Inglés
        'BF' => 'fr', // Burkina Faso - 24 millones - Francés
        'ML' => 'fr', // Malí - 22 millones - Francés
        'CI' => 'fr', // Costa de Marfil - 27 millones - Francés
        'CM' => 'fr', // Camerún - 28 millones - Francés
        'SD' => 'ar', // Sudán - 47 millones - Árabe
        'MA' => 'ar', // Marruecos - 37 millones - Árabe
        'TN' => 'ar', // Túnez - 13 millones - Árabe
        'LT' => 'lt', // Lituania - 3.5 millones - Lituano
        'BG' => 'bg', // Bulgaria - 6.9 millones - Búlgaro
        'RO' => 'ro', // Rumanía - 19 millones - Rumano
        'PL' => 'pl', // Polonia - 38 millones - Polaco
        'HU' => 'hu', // Hungría - 9 millones - Húngaro
        'HR' => 'hr', // Croacia - 4 millones - Croata
        'GR' => 'el', // Grecia - 10 millones - Griego
        'PT' => 'pt', // Portugal - 10 millones - Portugués
        'CZ' => 'cs', // Chequia - 10 millones - Checo
        'SI' => 'sl', // Eslovenia - 2 millones - Esloveno
        'SK' => 'sk', // Eslovaquia - 5 millones - Eslovaco
        'FI' => 'fi', // Finlandia - 5 millones - Finés
        'NO' => 'no', // Noruega - 5 millones - Noruego
        'SE' => 'sv', // Suecia - 10 millones - Sueco
        'DK' => 'da', // Dinamarca - 5 millones - Danés
    ];

    public const VALID_LANGUAGES = [
        'zh', // Chino Mandarín
        'hi', // Hindi
        'en', // Inglés
        'id', // Indonesio
        'pt', // Portugués
        'ur', // Urdu
        'am', // Amárico
        'es', // Español
        'tl', // Filipino
        'ar', // Árabe
        'ru', // Ruso
        'fr', // Francés
        'sw', // Suajili
        'th', // Tailandés
        'de', // Alemán
        'it', // Italiano
        'ko', // Coreano
        'my', // Birmano
        'bn', // Bengalí
        'uk', // Ucraniano
        'km', // Jemer
        'so', // Somali
        'rw', // Kinyarwanda
        'ht', // Criollo Haitiano
        'tk', // Turcomano
        'lt', // Lituano
        'bg', // Búlgaro
        'ro', // Rumano
        'pl', // Polaco
        'hu', // Húngaro
        'hr', // Croata
        'el', // Griego
        'cs', // Checo
        'sl', // Esloveno
        'sk', // Eslovaco
        'fi', // Finés
        'no', // Noruego
        'sv', // Sueco
        'da', // Danés
        'ca', // Catalán
    ];

    /**
     * Obtiene el idioma según la IP.
     *
     * @param string $ip
     * @return string
     */
    public function getLanguageByIp(string $ip): string
    {
        // Intentar obtener la ubicación basada en la IP
        try {
            $position = Location::get($ip);
            if ($position && isset($position->countryCode)) {
                $countryCode = $position->countryCode;
                return $this->getLanguageByCountry($countryCode);
            }
        } catch (\Exception $e) {
            // Manejo de errores si la geolocalización falla
            // Loguear el error si es necesario
        }

        // Devolver un idioma por defecto si no se puede obtener la geolocalización
        return 'es';
    }

    /**
     * Devuelve el idioma basado en el código del país.
     *
     * @param string $countryCode
     * @return string
     */
    private function getLanguageByCountry(string $countryCode): string
    {
        return $this->languageByCountry[$countryCode] ?? 'es'; // Idioma por defecto 'en'
    }

    private function getValidLanguage($language): ?string
    {
        return in_array($language, self::VALID_LANGUAGES) ? $language : null;
    }

    public function isValidLanguage($language): bool
    {
        return in_array($language, self::VALID_LANGUAGES);
    }

    public function getLocale(Request $request): string
    {
        // Recuperar una cookie por su nombre
        $preferredLang = $this->getValidLanguage($request->cookie('preferredLang'));
        if ($preferredLang) {
            $locale = $preferredLang;
        } else {
            // Obtener el idioma del encabezado Accept-Language
            $acceptLang = $this->getValidLanguage($request->getPreferredLanguage(self::VALID_LANGUAGES)); // Define los idiomas soportados
            if ($acceptLang) {
                $locale = $acceptLang;
            } else {
                // Obtener el idioma según la IP
                $ipLanguage = $this->getValidLanguage($this->getLanguageByIp($request->ip()));
                if ($ipLanguage) {
                    $locale = $ipLanguage;
                } else {
                    $locale = 'es';
                }
            }
        }
        return $locale;
    }

}
