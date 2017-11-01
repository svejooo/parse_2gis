<?php

class gisParse {

    private $key = '';
    private $region = 0;

    private $html = '';

    public static $cities = [

        69 => "Абакан",
        108 => "Альметьевск",
        106 => "Армавир",
        49 => "Архангельск",
        8 => "Астрахань",
        4 => "Барнаул",
        46 => "Белгород",
        20 => "Бийск",
        52 => "Благовещенск",
        51 => "Братск",
        62 => "Брянск",
        77 => "Великий", "Новгород",
        25 => "Владивосток",
        59 => "Владимир",
        33 => "Волгоград",
        78 => "Вологда",
        31 => "Воронеж",
        27 => "Горно-Алтайск",
        9 => "Екатеринбург",
        65 => "Иваново",
        41 => "Ижевск",
        11 => "Иркутск",
        70 => "Йошкар-Ола",
        21 => "Казань",
        40 => "Калининград",
        61 => "Калуга",
        109 => "Каменск-Уральский",
        5 => "Кемерово",
        58 => "Киров",
        94 => "Комсомольск-на-Амуре",
        34 => "Кострома",
        23 => "Краснодар",
        7 => "Красноярск",
        10 => "Курган",
        73 => "Курск",
        86 => "Ленинск-Кузнецкий",
        56 => "Липецк",
        26 => "Магнитогорск",
        113 => "Махачкала",
        87 => "Миасс", "и", "Златоуст",
        32 => "Москва",
        96 => "Мурманск",
        29 => "Набережные", "Челны",
        82 => "Находка",
        12 => "Нижневартовск",
        19 => "Нижний", "Новгород",
        45 => "Нижний", "Тагил",
        6 => "Новокузнецк",
        74 => "Новороссийск",
        1 => "Новосибирск",
        76 => "Норильск",
        103 => "Ноябрьск",
        2 => "Омск",
        48 => "Оренбург",
        71 => "Орёл",
        42 => "Пенза",
        16 => "Пермь",
        80 => "Петрозаводск",
        95 => "Петропавловск-Камчатский",
        90 => "Псков",
        89 => "Пятигорск",
        24 => "Ростов-на-Дону",
        44 => "Рязань",
        18 => "Самара",
        38 => "Санкт-Петербург",
        85 => "Саранск",
        43 => "Саратов",
        63 => "Смоленск",
        30 => "Сочи",
        57 => "Ставрополь",
        60 => "Старый", "Оскол",
        54 => "Стерлитамак",
        39 => "Сургут",
        72 => "Сыктывкар",
        81 => "Тамбов",
        47 => "Тверь",
        97 => "Тобольск",
        22 => "Тольятти",
        3 => "Томск",
        36 => "Тула",
        13 => "Тюмень",
        37 => "Улан-Удэ",
        55 => "Ульяновск",
        83 => "Уссурийск",
        17 => "Уфа",
        35 => "Хабаровск",
        53 => "Чебоксары",
        15 => "Челябинск",
        64 => "Чита",
        88 => "Южно-Сахалинск",
        50 => "Якутск",
        28 => "Ярославль",
    ];

    public function __construct($key, $region) {
        $this->key = $key;
        $this->region = $region;
    }

    public static function listCities() {
        foreach (self::$cities as $id => $city) {
            echo $city . ' = ' . $id . "\n";
        }
    }

    public function getRubrics() {
        $url = "https://catalog.api.2gis.ru/2.0/catalog/rubric/list?parent_id=0&region_id={$this->region}&sort=popularity&fields=items.rubrics&key={$this->key}";
        return json_decode(file_get_contents($url), true);
    }

    public function getOrganizations($rubric) {
        $url = "https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=1&page_size=12&rubric_id={$rubric}&hash=da74bec932430537&stat%5Bpr%5D=3&region_id={$this->region}&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Ccontext_rubrics%2Chash%2Csearch_attributes&key={$this->key}";
        return json_decode(file_get_contents($url), true);
    }

    public function parseToHtml() {
        $rubrics = $this->getRubrics();
        $this->clearTable();
        $this->addRubHeader();


        if (isset($rubrics['result']['items'])) {
            
            foreach ($rubrics['result']['items'] as $rubric) {
                $this->addRubName($rubric['name']);

                echo $rubric['name'] . ":\n";

                foreach ($rubric['rubrics'] as $sub) {
                    echo ' - ' . $sub['name'] . "\n";

                    $organizations = $this->getOrganizations($sub['id']);

                    if (isset($organizations['result']['items'])) {
                        foreach ($organizations['result']['items'] as $org) {

                            $contacts = [];

                            if (isset($org['contact_groups'][0]))
                                foreach ($org['contact_groups'][0]['contacts'] as $contact){
                                    $contacts[] = $contact['text'];
                                }

                            $this->addRubRow($sub['name'], $org['name'], @$org['address_name'], $contacts);
                        }
                    }
                }
            }
        }

        echo "Parse complete! \n";

        $this->save();
    }

    private function clearTable() {
        $this->html = '';
    }

    private function addRubName($str) {
        $this->html .= "<tr><td><br></td><td><br></td><td><br></td><td><br></td></tr>";
        $this->html .= "<tr><td colspan=4 height=\"23\" align=\"left\"><b>Рубрика: {$str}</b></td></tr>";
        $this->html .= "<tr><td><br></td><td><br></td><td><br></td><td><br></td></tr>";
    }

    private function addRubHeader() {
        $this->html .= "<tr><td align=\"left\" bgcolor=\"#EEEEEE\"><b>Категория</b></td><td align=\"left\" bgcolor=\"#EEEEEE\"><b>Название</b></td><td align=\"left\" bgcolor=\"#EEEEEE\"><b>Адрес</b></td><td align=\"left\" bgcolor=\"#EEEEEE\"><b>Контакты</b></td></tr>";
    }

    private function addRubRow($cat, $name, $addr, $contacts) {
        $cnt = implode(", ", $contacts);

        $this->html .= "<tr><td align=\"left\">{$cat}</td><td align=\"left\">{$name}</td><td align=\"left\">{$addr}</td><td align=\"left\">{$cnt}</td></tr>";
    }

    private function save() {
        $date = date('Y.m.d-H-i');
        $html =
            <<<HTML
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title>2Gis parse log {$date}</title>
</head>
<body>
<table cellspacing="0" border="0">
{$this->html}	
</table>
</body>
</html>
HTML;

        file_put_contents($date . '-log.html', $html);
    }
}

/* */
echo "2Gis parsser: \n";
echo "Use: php index.php <region> <key> \n";
echo "P.S. You can get key from the 2gis page (webApiKey).";
echo "\n";
echo "\n";

if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Error: invalid <key> or <region>\n";

    gisParse::listCities();
} else {
    $parse = new gisParse($argv[2], $argv[1]);
    $parse->parseToHtml();
}

?>
