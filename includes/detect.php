'Sony'          => 'SonyST|SonyLT|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i|LT28h|LT26w|SonyEricssonMT27i|C5303|C6902|C6903|C6906|C6943|D2533|SOV34|601SO|F8332',
        'Asus'          => 'Asus.*Galaxy|PadFone.*Mobile',
        'Xiaomi'        => 'HM NOTE 1LTETD|HM NOTE 1S|HM NOTE 1W|Mi Note Pro|Mi 4i|MI 6|MI MAX|Mi 5s Plus|Mi 9|MI 9 SE|MI 9T|MI 9T Pro|Redmi Note 7|Redmi 6A|Redmi 6 Pro|Redmi Note 6 Pro|Redmi Note 5|Redmi S2|POCOPHONE F1',
        'Huawei'        => 'Huawei|Ascend|HUAWEI-|Honor|HW-|Nexus 6P|EVA-|P9|P10|P20|P30|EVA-AL00|EVA-L09|VIE-AL10|VOG-L29',
        'OnePlus'       => 'ONEPLUS',
        'Generic'       => 'Tablet|BntV8OTT|MID-WCDMA|LogicPD Zoom2|A7TA|AT.BA|AT-AT|CT695|CT888|DT8PL|DTBP801C|DTP810C|EV-TB|M758A|N83|NM81|P-CTV|P-GD|P-GS|Pc701|TA102|TA103|TA104|TA105|TA106|TA107|TA108|TA109|TA110|TA111|TA112|TA113|TA114|TA115|TA116|TA117|TA118|TA119|TA120|TA121|TA122|TA123|TA124|TBD|TBD.*B15|TBD.*B16|TBD804DC|TBD807LC|TBD810LC|TBD811LC|TBD812LC|TBD820HD|TQC.*A101|UMPC|UMPC-New|X10MK|X11MK|XY8PTK|ZX8PTK|Mobile|Android|dalvik|opera mini|screen/i'
    );

    // OS và platforms
    protected $operatingSystems = array(
        'AndroidOS'     => 'Android',
        'BlackBerryOS'  => 'blackberry|\bBB10\b|rim tablet os',
        'iOS'           => '\biPhone\b|\biPad\b|\biPod\b',
        'WindowsPhone'  => 'Windows Phone|Windows Mobile',
        'WindowsRT'     => 'Windows RT',
        'WindowsNT'     => 'Windows NT',
        'MeeGoOS'       => 'MeeGo',
        'MaemoOS'       => 'Maemo',
        'JavaOS'        => 'J2ME/|\bMIDP\b|\bCLDC\b',
        'webOS'         => 'webOS|hpwOS',
        'badaOS'        => '\bBada\b',
        'BREWOS'        => 'BREW',
        'Chrome OS'     => 'CrOS',
    );

    // List of mobile browsers
    protected $browsers = array(
        'Chrome'        => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
        'Opera'         => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR/[0-9.]+|Coast/[0-9.]+',
        'Edge'          => 'Mobile Safari/[.0-9]* Edge|Edg/[.0-9]* Mobile|Mobile.*Edg/[.0-9]*',
        'Firefox'       => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS',
        'Safari'        => 'Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari',
        'UCBrowser'     => 'UC.*Browser|UCWEB',
        'MQQBrowser'    => 'MQQBrowser',
        'headlessBrowser' => 'Headless',
        'YaBrowser'     => 'YaBrowser',
        'SamsungBrowser' => 'SamsungBrowser',
        'InternetExplorer' => 'IEMobile|MSIEMobile',
    );

    public function __construct() {
        $this->setHttpHeaders();
        $this->setUserAgent();
    }

    /**
     * Set HTTP headers
     */
    public function setHttpHeaders() {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $this->httpHeaders[$key] = $value;
            }
        }
    }

    /**
     * Set User-Agent
     */
    public function setUserAgent() {
        if (isset($this->httpHeaders['HTTP_USER_AGENT'])) {
            $this->userAgent = $this->httpHeaders['HTTP_USER_AGENT'];
        } else {
            $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        }
    }

    /**
     * Phát hiện thiết bị di động
     * @return bool
     */
    public function isMobile() {
        if (empty($this->userAgent)) {
            return false;
        }

        foreach ($this->mobileDevices as $device => $match) {
            if (preg_match('/' . $match . '/i', $this->userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Phát hiện tablet
     * @return bool
     */
    public function isTablet() {
        if (empty($this->userAgent)) {
            return false;
        }

        // Check for iPad, Android tablets
        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet|PlayBook/i', $this->userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Phát hiện desktop
     * @return bool
     */
    public function isDesktop() {
        return !$this->isMobile() && !$this->isTablet();
    }

    /**
     * Lấy tên và phiên bản trình duyệt
     * @return array
     */
    public function getBrowser() {
        $browser = 'Unknown';
        $version = '';

        foreach ($this->browsers as $key => $value) {
            if (preg_match('/' . $value . '/i', $this->userAgent)) {
                $browser = $key;
                
                // Try to get version
                if (preg_match('/(' . $key . ')\/([0-9\.]+)/i', $this->userAgent, $matches)) {
                    $version = $matches[2];
                }
                
                break;
            }
        }

        return ['name' => $browser, 'version' => $version];
    }

    /**
     * Lấy tên và phiên bản hệ điều hành
     * @return array
     */
    public function getOS() {
        $os = 'Unknown';
        $version = '';

        foreach ($this->operatingSystems as $key => $value) {
            if (preg_match('/' . $value . '/i', $this->userAgent)) {
                $os = $key;
                
                // Try to get version
                if (preg_match('/(' . $value . ') ([0-9\.]+)/i', $this->userAgent, $matches)) {
                    $version = $matches[2];
                }
                
                break;
            }
        }

        return ['name' => $os, 'version' => $version];
    }

    /**
     * Lấy loại thiết bị
     * @return string
     */
    public function getDeviceType() {
        if ($this->isTablet()) {
            return 'Tablet';
        } elseif ($this->isMobile()) {
            return 'Mobile';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Lấy tên hãng thiết bị
     * @return string
     */
    public function getDeviceVendor() {
        $vendor = 'Unknown';

        foreach ($this->mobileDevices as $key => $value) {
            if (preg_match('/' . $value . '/i', $this->userAgent)) {
                $vendor = $key;
                break;
            }
        }

        return $vendor;
    }

    /**
     * Phát hiện loại kết nối mạng
     * @return string
     */
    public function getNetworkType() {
        // Lưu ý: Đây chỉ là ước tính từ User-Agent, không thể chính xác 100%
        if (strpos($this->userAgent, 'WIFI') !== false) {
            return 'Wifi';
        } elseif (strpos($this->userAgent, '4G') !== false) {
            return '4G';
        } elseif (strpos($this->userAgent, '3G') !== false) {
            return '3G';
        } elseif (strpos($this->userAgent, '2G') !== false) {
            return '2G';
        }

        return 'Unknown';
    }
}
'Samsung'       => 'Samsung|SM-G9250|GT-19300|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9082|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9305|GT-I9500|GT-I9505|GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S7710|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-I959|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100|GT-N7105|SCH-I535|SM-N900A|SGH-I317|SGH-T999L|GT-S5360B|GT-I8262|GT-S6802|GT-S6312|GT-S6310|GT-S5312|GT-S5310|GT-I9105|GT-I8510|GT-S6790N|SM-G7105|SM-N9005|GT-S5301|GT-I9295|GT-I9195|SM-C101|GT-S7392|GT-S7560|GT-B7610|GT-I5510|GT-S7582|GT-S7530E|GT-I8750|SM-G9006V|SM-G9008V|SM-G9009D|SM-G900A|SM-G900D|SM-G900F|SM-G900H|SM-G900I|SM-G900J|SM-G900K|SM-G900L|SM-G900M|SM-G900P|SM-G900R4|SM-G900S|SM-G900T|SM-G900V|SM-G900W8|SHV-E160K|SCH-P709|SCH-P729|SM-T2558|GT-I9205',
        'LG'            => '\bLG\b|LG[- ]?(C800|C900|E400|E700|Elec|Connection|JOJO|GS290|GS390|GS500|GS505|GS700|GS505|GS505|C710|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|730|855|L160|LS740|LS840|LS970|LU705|LU801|LU810|LM-G710|LM-V500|LM-X420|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P500|P509|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999|E612|D955|D802|MS323)',
        'Sony'          => 'SonyST|SonyLT|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i|LT28h|LT26w|SonyEricssonMT27i|C5303|C6902|C6<?php
/**
 * Mobile Detect class
 *
 * Phát hiện thiết bị, trình duyệt di động
 * Phiên bản đơn giản hóa từ http://mobiledetect.net
 */

class Mobile_Detect {
    protected $userAgent = null;
    protected $httpHeaders = array();
    
    // List of mobile devices
    protected $mobileDevices = array(
        'iPhone'        => '\biPhone\b|\biPod\b',
        'BlackBerry'    => 'BlackBerry|\bBB10\b|rim[0-9]+',
        'HTC'           => 'HTC|HTC.*(Sensation|Evo|Vision|Explorer|6800|8100|8900|A7272|S510e|C110e|Legend|Desire|T8282)',
        'Nexus'         => 'Nexus One|Nexus S|Galaxy.*Nexus|Android.*Nexus.*Mobile',
        'Dell'          => 'Dell.*Streak|Dell.*Aero|Dell.*Venue|DELL.*Venue Pro|Dell Flash|Dell Smoke|Dell Mini 3iX|XCD28|XCD35',
        'Motorola'      => 'Motorola|DROIDX|DROID BIONIC|\bDroid\b.*Build|Android.*Xoom|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT901|XT907|XT909|XT910|XT912|XT928|XT926|XT915|XT919|XT925',
        'Samsung'       => 'Samsung|SM-G9250|GT-19300|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9082|GT-I9100|GT-I9103|GT-I9220|