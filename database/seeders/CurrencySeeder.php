<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::updateOrCreate(
            ['code' => 'AED'],
            [
                'number' => '784',
                'digits' => 2,
                'currency' => "UAE Dirham",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AFN'],
            [
                'number' => '971',
                'digits' => 2,
                'currency' => "Afghani",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ALL'],
            [
                'number' => '008',
                'digits' => 2,
                'currency' => "Lek",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AMD'],
            [
                'number' => '051',
                'digits' => 2,
                'currency' => "Armenian Dram",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ANG'],
            [
                'number' => '532',
                'digits' => 2,
                'currency' => "Netherlands Antillean Guilder",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AOA'],
            [
                'number' => '973',
                'digits' => 2,
                'currency' => "Kwanza",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ARS'],
            [
                'number' => '032',
                'digits' => 2,
                'currency' => "Argentine Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AUD'],
            [
                'number' => '036',
                'digits' => 2,
                'currency' => "Australian Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AWG'],
            [
                'number' => '533',
                'digits' => 2,
                'currency' => "Aruban Florin",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'AZN'],
            [
                'number' => '944',
                'digits' => 2,
                'currency' => "Azerbaijan Manat",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BAM'],
            [
                'number' => '977',
                'digits' => 2,
                'currency' => "Convertible Mark",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BBD'],
            [
                'number' => '052',
                'digits' => 2,
                'currency' => "Barbados Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BDT'],
            [
                'number' => '050',
                'digits' => 2,
                'currency' => "Taka",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BGN'],
            [
                'number' => '975',
                'digits' => 2,
                'currency' => "Bulgarian Lev",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BHD'],
            [
                'number' => '048',
                'digits' => 3,
                'currency' => "Bahraini Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BIF'],
            [
                'number' => '108',
                'digits' => 0,
                'currency' => "Burundi Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BMD'],
            [
                'number' => '060',
                'digits' => 2,
                'currency' => "Bermudian Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BND'],
            [
                'number' => '096',
                'digits' => 2,
                'currency' => "Brunei Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BOB'],
            [
                'number' => '068',
                'digits' => 2,
                'currency' => "Boliviano",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BOV'],
            [
                'number' => '984',
                'digits' => 2,
                'currency' => "Mvdol",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BRL'],
            [
                'number' => '986',
                'digits' => 2,
                'currency' => "Brazilian Real",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BSD'],
            [
                'number' => '044',
                'digits' => 2,
                'currency' => "Bahamian Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BTN'],
            [
                'number' => '064',
                'digits' => 2,
                'currency' => "Ngultrum",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BWP'],
            [
                'number' => '072',
                'digits' => 2,
                'currency' => "Pula",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BYN'],
            [
                'number' => '933',
                'digits' => 2,
                'currency' => "Belarusian Ruble",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'BZD'],
            [
                'number' => '084',
                'digits' => 2,
                'currency' => "Belize Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CAD'],
            [
                'number' => '124',
                'digits' => 2,
                'currency' => "Canadian Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CDF'],
            [
                'number' => '976',
                'digits' => 2,
                'currency' => "Congolese Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CHE'],
            [
                'number' => '947',
                'digits' => 2,
                'currency' => "WIR Euro",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CHF'],
            [
                'number' => '756',
                'digits' => 2,
                'currency' => "Swiss Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CHW'],
            [
                'number' => '948',
                'digits' => 2,
                'currency' => "WIR Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CLF'],
            [
                'number' => '990',
                'digits' => 4,
                'currency' => "Unidad de Fomento",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CLP'],
            [
                'number' => '152',
                'digits' => 0,
                'currency' => "Chilean Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CNY'],
            [
                'number' => '156',
                'digits' => 2,
                'currency' => "Chinese Yuan Renminbi",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'COP'],
            [
                'number' => '170',
                'digits' => 2,
                'currency' => "Colombian Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'COU'],
            [
                'number' => '970',
                'digits' => 2,
                'currency' => "Unidad de Valor Real",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CRC'],
            [
                'number' => '188',
                'digits' => 2,
                'currency' => "Costa Rican Colon",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CUC'],
            [
                'number' => '931',
                'digits' => 2,
                'currency' => "Peso Convertible",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CUP'],
            [
                'number' => '192',
                'digits' => 2,
                'currency' => "Cuban Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CVE'],
            [
                'number' => '132',
                'digits' => 2,
                'currency' => "Cabo Verde Escudo",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'CZK'],
            [
                'number' => '203',
                'digits' => 2,
                'currency' => "Czech Koruna",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'DJF'],
            [
                'number' => '262',
                'digits' => 0,
                'currency' => "Djibouti Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'DKK'],
            [
                'number' => '208',
                'digits' => 2,
                'currency' => "Danish Krone",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'DOP'],
            [
                'number' => '214',
                'digits' => 2,
                'currency' => "Dominican Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'DZD'],
            [
                'number' => '012',
                'digits' => 2,
                'currency' => "Algerian Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'EGP'],
            [
                'number' => '818',
                'digits' => 2,
                'currency' => "Egyptian Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ERN'],
            [
                'number' => '232',
                'digits' => 2,
                'currency' => "Nakfa",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ETB'],
            [
                'number' => '230',
                'digits' => 2,
                'currency' => "Ethiopian Birr",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'EUR'],
            [
                'number' => '978',
                'digits' => 2,
                'currency' => "Euro",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'FJD'],
            [
                'number' => '242',
                'digits' => 2,
                'currency' => "Fiji Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'FKP'],
            [
                'number' => '238',
                'digits' => 2,
                'currency' => "Falkland Islands Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GBP'],
            [
                'number' => '826',
                'digits' => 2,
                'currency' => "Pound Sterling",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GEL'],
            [
                'number' => '981',
                'digits' => 2,
                'currency' => "Lari",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GHS'],
            [
                'number' => '936',
                'digits' => 2,
                'currency' => "Ghana Cedi",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GIP'],
            [
                'number' => '292',
                'digits' => 2,
                'currency' => "Gibraltar Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GMD'],
            [
                'number' => '270',
                'digits' => 2,
                'currency' => "Dalasi",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GNF'],
            [
                'number' => '324',
                'digits' => 0,
                'currency' => "Guinean Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GTQ'],
            [
                'number' => '320',
                'digits' => 2,
                'currency' => "Quetzal",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'GYD'],
            [
                'number' => '328',
                'digits' => 2,
                'currency' => "Guyana Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'HKD'],
            [
                'number' => '344',
                'digits' => 2,
                'currency' => "Hong Kong Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'HNL'],
            [
                'number' => '340',
                'digits' => 2,
                'currency' => "Lempira",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'HRK'],
            [
                'number' => '191',
                'digits' => 2,
                'currency' => "Kuna",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'HTG'],
            [
                'number' => '332',
                'digits' => 2,
                'currency' => "Gourde",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'HUF'],
            [
                'number' => '348',
                'digits' => 2,
                'currency' => "Forint",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'IDR'],
            [
                'number' => '360',
                'digits' => 2,
                'currency' => "Rupiah",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ILS'],
            [
                'number' => '376',
                'digits' => 2,
                'currency' => "New Israeli Sheqel",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'INR'],
            [
                'number' => '356',
                'digits' => 2,
                'currency' => "Indian Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'IQD'],
            [
                'number' => '368',
                'digits' => 3,
                'currency' => "Iraqi Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'IRR'],
            [
                'number' => '364',
                'digits' => 2,
                'currency' => "Iranian Rial",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ISK'],
            [
                'number' => '352',
                'digits' => 0,
                'currency' => "Iceland Krona",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'JMD'],
            [
                'number' => '388',
                'digits' => 2,
                'currency' => "Jamaican Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'JOD'],
            [
                'number' => '400',
                'digits' => 3,
                'currency' => "Jordanian Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'JPY'],
            [
                'number' => '392',
                'digits' => 0,
                'currency' => "Yen",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KES'],
            [
                'number' => '404',
                'digits' => 2,
                'currency' => "Kenyan Shilling",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KGS'],
            [
                'number' => '417',
                'digits' => 2,
                'currency' => "Som",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KHR'],
            [
                'number' => '116',
                'digits' => 2,
                'currency' => "Riel",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KMF'],
            [
                'number' => '174',
                'digits' => 0,
                'currency' => "Comorian Franc ",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KPW'],
            [
                'number' => '408',
                'digits' => 2,
                'currency' => "North Korean Won",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KRW'],
            [
                'number' => '410',
                'digits' => 0,
                'currency' => "Won",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KWD'],
            [
                'number' => '414',
                'digits' => 3,
                'currency' => "Kuwaiti Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KYD'],
            [
                'number' => '136',
                'digits' => 2,
                'currency' => "Cayman Islands Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'KZT'],
            [
                'number' => '398',
                'digits' => 2,
                'currency' => "Tenge",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LAK'],
            [
                'number' => '418',
                'digits' => 2,
                'currency' => "Lao Kip",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LBP'],
            [
                'number' => '422',
                'digits' => 2,
                'currency' => "Lebanese Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LKR'],
            [
                'number' => '144',
                'digits' => 2,
                'currency' => "Sri Lanka Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LRD'],
            [
                'number' => '430',
                'digits' => 2,
                'currency' => "Liberian Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LSL'],
            [
                'number' => '426',
                'digits' => 2,
                'currency' => "Loti",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'LYD'],
            [
                'number' => '434',
                'digits' => 3,
                'currency' => "Libyan Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MAD'],
            [
                'number' => '504',
                'digits' => 2,
                'currency' => "Moroccan Dirham",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MDL'],
            [
                'number' => '498',
                'digits' => 2,
                'currency' => "Moldovan Leu",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MGA'],
            [
                'number' => '969',
                'digits' => 2,
                'currency' => "Malagasy Ariary",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MKD'],
            [
                'number' => '807',
                'digits' => 2,
                'currency' => "Denar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MMK'],
            [
                'number' => '104',
                'digits' => 2,
                'currency' => "Kyat",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MNT'],
            [
                'number' => '496',
                'digits' => 2,
                'currency' => "Tugrik",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MOP'],
            [
                'number' => '446',
                'digits' => 2,
                'currency' => "Pataca",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MRU'],
            [
                'number' => '929',
                'digits' => 2,
                'currency' => "Ouguiya",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MUR'],
            [
                'number' => '480',
                'digits' => 2,
                'currency' => "Mauritius Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MVR'],
            [
                'number' => '462',
                'digits' => 2,
                'currency' => "Rufiyaa",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MWK'],
            [
                'number' => '454',
                'digits' => 2,
                'currency' => "Malawi Kwacha",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MXN'],
            [
                'number' => '484',
                'digits' => 2,
                'currency' => "Mexican Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MXV'],
            [
                'number' => '979',
                'digits' => 2,
                'currency' => "Mexican Unidad de Inversion (UDI)",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MYR'],
            [
                'number' => '458',
                'digits' => 2,
                'currency' => "Malaysian Ringgit",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'MZN'],
            [
                'number' => '943',
                'digits' => 2,
                'currency' => "Mozambique Metical",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NAD'],
            [
                'number' => '516',
                'digits' => 2,
                'currency' => "Namibia Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NGN'],
            [
                'number' => '566',
                'digits' => 2,
                'currency' => "Naira",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NIO'],
            [
                'number' => '558',
                'digits' => 2,
                'currency' => "Cordoba Oro",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NOK'],
            [
                'number' => '578',
                'digits' => 2,
                'currency' => "Norwegian Krone",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NPR'],
            [
                'number' => '524',
                'digits' => 2,
                'currency' => "Nepalese Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'NZD'],
            [
                'number' => '554',
                'digits' => 2,
                'currency' => "New Zealand Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'OMR'],
            [
                'number' => '512',
                'digits' => 3,
                'currency' => "Rial Omani",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PAB'],
            [
                'number' => '590',
                'digits' => 2,
                'currency' => "Balboa",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PEN'],
            [
                'number' => '604',
                'digits' => 2,
                'currency' => "Sol",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PGK'],
            [
                'number' => '598',
                'digits' => 2,
                'currency' => "Kina",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PHP'],
            [
                'number' => '608',
                'digits' => 2,
                'currency' => "Philippine Peso",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PKR'],
            [
                'number' => '586',
                'digits' => 2,
                'currency' => "Pakistan Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PLN'],
            [
                'number' => '985',
                'digits' => 2,
                'currency' => "Zloty",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'PYG'],
            [
                'number' => '600',
                'digits' => 0,
                'currency' => "Guarani",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'QAR'],
            [
                'number' => '634',
                'digits' => 2,
                'currency' => "Qatari Rial",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'RON'],
            [
                'number' => '946',
                'digits' => 2,
                'currency' => "Romanian Leu",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'RSD'],
            [
                'number' => '941',
                'digits' => 2,
                'currency' => "Serbian Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'RUB'],
            [
                'number' => '643',
                'digits' => 2,
                'currency' => "Russian Ruble",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'RWF'],
            [
                'number' => '646',
                'digits' => 0,
                'currency' => "Rwanda Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SAR'],
            [
                'number' => '682',
                'digits' => 2,
                'currency' => "Saudi Riyal",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SBD'],
            [
                'number' => '090',
                'digits' => 2,
                'currency' => "Solomon Islands Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SCR'],
            [
                'number' => '690',
                'digits' => 2,
                'currency' => "Seychelles Rupee",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SDG'],
            [
                'number' => '938',
                'digits' => 2,
                'currency' => "Sudanese Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SEK'],
            [
                'number' => '752',
                'digits' => 2,
                'currency' => "Swedish Krona",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SGD'],
            [
                'number' => '702',
                'digits' => 2,
                'currency' => "Singapore Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SHP'],
            [
                'number' => '654',
                'digits' => 2,
                'currency' => "Saint Helena Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SLL'],
            [
                'number' => '694',
                'digits' => 2,
                'currency' => "Leone",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SOS'],
            [
                'number' => '706',
                'digits' => 2,
                'currency' => "Somali Shilling",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SRD'],
            [
                'number' => '968',
                'digits' => 2,
                'currency' => "Surinam Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SSP'],
            [
                'number' => '728',
                'digits' => 2,
                'currency' => "South Sudanese Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'STN'],
            [
                'number' => '930',
                'digits' => 2,
                'currency' => "Dobra",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SVC'],
            [
                'number' => '222',
                'digits' => 2,
                'currency' => "El Salvador Colon",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SYP'],
            [
                'number' => '760',
                'digits' => 2,
                'currency' => "Syrian Pound",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'SZL'],
            [
                'number' => '748',
                'digits' => 2,
                'currency' => "Lilangeni",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'THB'],
            [
                'number' => '764',
                'digits' => 2,
                'currency' => "Baht",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TJS'],
            [
                'number' => '972',
                'digits' => 2,
                'currency' => "Somoni",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TMT'],
            [
                'number' => '934',
                'digits' => 2,
                'currency' => "Turkmenistan New Manat",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TND'],
            [
                'number' => '788',
                'digits' => 3,
                'currency' => "Tunisian Dinar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TOP'],
            [
                'number' => '776',
                'digits' => 2,
                'currency' => "Pa’anga",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TRY'],
            [
                'number' => '949',
                'digits' => 2,
                'currency' => "Turkish Lira",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TTD'],
            [
                'number' => '780',
                'digits' => 2,
                'currency' => "Trinidad and Tobago Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TWD'],
            [
                'number' => '901',
                'digits' => 2,
                'currency' => "New Taiwan Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'TZS'],
            [
                'number' => '834',
                'digits' => 2,
                'currency' => "Tanzanian Shilling",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UAH'],
            [
                'number' => '980',
                'digits' => 2,
                'currency' => "Hryvnia",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UGX'],
            [
                'number' => '800',
                'digits' => 0,
                'currency' => "Uganda Shilling",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'USD'],
            [
                'number' => '840',
                'digits' => 2,
                'currency' => "US Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'USN'],
            [
                'number' => '997',
                'digits' => 2,
                'currency' => "US Dollar (Next day)",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UYI'],
            [
                'number' => '940',
                'digits' => 0,
                'currency' => "Uruguay Peso en Unidades Indexadas (UI)",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UYU'],
            [
                'number' => '858',
                'digits' => 2,
                'currency' => "Peso Uruguayo",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UYW'],
            [
                'number' => '927',
                'digits' => 4,
                'currency' => "Unidad Previsional",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'UZS'],
            [
                'number' => '860',
                'digits' => 2,
                'currency' => "Uzbekistan Sum",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'VES'],
            [
                'number' => '928',
                'digits' => 2,
                'currency' => "Bolívar Soberano",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'VND'],
            [
                'number' => '704',
                'digits' => 0,
                'currency' => "Dong",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'VUV'],
            [
                'number' => '548',
                'digits' => 0,
                'currency' => "Vatu",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'WST'],
            [
                'number' => '882',
                'digits' => 2,
                'currency' => "Tala",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XAF'],
            [
                'number' => '950',
                'digits' => 0,
                'currency' => "CFA Franc BEAC",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XCD'],
            [
                'number' => '951',
                'digits' => 2,
                'currency' => "East Caribbean Dollar",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XDR'],
            [
                'number' => '960',
                'digits' => 0,
                'currency' => "SDR (Special Drawing Right)",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XOF'],
            [
                'number' => '952',
                'digits' => 0,
                'currency' => "CFA Franc BCEAO",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XPF'],
            [
                'number' => '953',
                'digits' => 0,
                'currency' => "CFP Franc",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XSU'],
            [
                'number' => '994',
                'digits' => 0,
                'currency' => "Sucre",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'XUA'],
            [
                'number' => '965',
                'digits' => 0,
                'currency' => "ADB Unit of Account",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'YER'],
            [
                'number' => '886',
                'digits' => 2,
                'currency' => "Yemeni Rial",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ZAR'],
            [
                'number' => '710',
                'digits' => 2,
                'currency' => "Rand",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ZMW'],
            [
                'number' => '967',
                'digits' => 2,
                'currency' => "Zambian Kwacha",
            ]
        );
        Currency::updateOrCreate(
            ['code' => 'ZWL'],
            [
                'number' => '932',
                'digits' => 2,
                'currency' => "Zimbabwe Dollar",
            ]
        );
    }
}
