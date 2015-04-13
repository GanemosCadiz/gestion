<?php

/**
 * localization
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez
 * @copyright 2015 Pablo Fernandez
 * @version 0.01 // 2015-01-18
 * @access public
 */

/**
 * Changelog:
 * 2015-01-18:	Added localization::getLangName() to get language name.
 * 2015-01-15: 	Added localization::numeric info by localeconv().
 * 							Added localization:checkLang().
 * 2015-01-10: 	First version.
 */

class localization {
	
	private $default = "es";
	public $allowed = array("es");
	
	public $lang;
	public $numeric;
	
	/**
  ISO 639-1 Language Codes
  Useful in Locale analysis
   
  References :
  1. http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
  2. http://blog.xoundboy.com/?p=235
	*/
	private $locales = array(
	  'aa' => array("", "", "", "", ""), // Afar
	  'ab' => array("", "", "", "", ""), // Abkhazian
	  'af' => array("Afrikáans", "Afrikaans", "af_ZA.UTF-8", "Afrikaans_South Africa.1252", "WINDOWS-1252"), // Afrikaans
	  'am' => array("", "", "", "", ""), // Amharic
	  'ar' => array("Árabe", "اللغة العربية", "ar_SA.UTF-8", "Arabic_Saudi Arabia.1256", "WINDOWS-1256"), // Arabic
	  'as' => array("", "", "", "", ""), // Assamese
	  'ay' => array("", "", "", "", ""), // Aymara
	  'az' => array("", "", "", "", ""), // Azerbaijani
	  'ba' => array("", "", "", "", ""), // Bashkir
	  'be' => array("Bielorruso", "Беларуская мова", "be_BY.UTF-8", "Belarusian_Belarus.1251", "WINDOWS-1251"), // Byelorussian
	  'bg' => array("Búlgaro", "български език", "bg_BG.UTF-8", "Bulgarian_Bulgaria.1251", "WINDOWS-1251"), // Bulgarian
	  'bh' => array("", "", "", "", ""), // Bihari
	  'bi' => array("", "", "", "", ""), // Bislama
	  'bn' => array("", "", "", "", ""), // Bengali/Bangla
	  'bo' => array("", "", "", "", ""), // Tibetan
	  'br' => array("", "", "", "", ""), // Breton
	  'ca' => array("Catalán", "Català", "ca_ES.UTF-8", "Catalan_Spain.1252", "WINDOWS-1252"), // Catalan
	  'co' => array("", "", "", "", ""), // Corsican
	  'cs' => array("Checo", "Čeština", "cs_CZ.UTF-8", "Czech_Czech Republic.1250", "WINDOWS-1250"), // Czech
	  'cy' => array("", "", "", "", ""), // Welsh
	  'da' => array("Danés", "Dansk", "da_DK.UTF-8", "Danish_Denmark.1252", "WINDOWS-1252"), // Danish
	  'de' => array("Alemán", "Deutsch", "de_DE.UTF-8", "German_Germany.1252", "WINDOWS-1252"), // German
	  'dz' => array("", "", "", "", ""), // Bhutani
	  'el' => array("Griego", "Ελληνικά", "el_GR.UTF-8", "Greek_Greece.1253", "WINDOWS-1253"), // Greek
	  'en' => array("Inglés", "English", "en.UTF-8", "English_Australia.1252", ""), // English
	  'eo' => array("", "", "", "", ""), // Esperanto
	  'es' => array("Español", "Español", "es_ES.UTF-8", "Spanish_Spain.1252", "WINDOWS-1252"), // Spanish
	  'et' => array("Estonio", "Eesti", "et_EE.UTF-8", "Estonian_Estonia.1257", "WINDOWS-1257"), // Estonian
	  'eu' => array("Vasco", "Euskara", "eu_ES.UTF-8", "Basque_Spain.1252", "WINDOWS-1252"), // Basque
	  'fa' => array("Persa", "فارسی", "fa_IR.UTF-8", "Farsi_Iran.1256", "WINDOWS-1256"), // Persian
	  'fi' => array("Finlandés", "Suomi", "fi_FI.UTF-8", "Finnish_Finland.1252", "WINDOWS-1252"), // Finnish
	  'fj' => array("", "", "", "", ""), // Fiji
	  'fo' => array("", "", "", "", ""), // Faeroese
	  'fr' => array("Francés", "Français", "fr_FR.UTF-8", "French_France.1252", "WINDOWS-1252"), // French
	  'fy' => array("", "", "", "", ""), // Frisian
	  'ga' => array("Irlandés", "Gaeilge", "ga.UTF-8", "Gaelic; Scottish Gaelic", "WINDOWS-1252"), // Irish
	  'gd' => array("", "", "", "", ""), // Scots/Gaelic
	  'gl' => array("Gallego", "Galego", "gl_ES.UTF-8", "Galician_Spain.1252", "WINDOWS-1252"), // Galician
	  'gn' => array("", "", "", "", ""), // Guarani
	  'gu' => array("Gujaratí", "ગુજરાતી", "gu.UTF-8", "Gujarati_India.0", ""), // Gujarati
	  'ha' => array("", "", "", "", ""), // Hausa
	  'hi' => array("Hindi", "हिन्दी", "hi_IN.UTF-8", "Hindi.65001", ""), // Hindi
	  'hr' => array("Croata", "Hrvatski", "hr_HR.UTF-8", "Croatian_Croatia.1250", "WINDOWS-1250"), // Croatian
	  'hu' => array("Húngaro", "Magyar", "hu.UTF-8", "Hungarian_Hungary.1250", "WINDOWS-1250"), // Hungarian
	  'hy' => array("", "", "", "", ""), // Armenian
	  'ia' => array("", "", "", "", ""), // Interlingua
	  'ie' => array("", "", "", "", ""), // Interlingue
	  'ik' => array("", "", "", "", ""), // Inupiak
	  'in' => array("Indonesio", "Bahasa Indonesia", "id_ID.UTF-8", "Indonesian_indonesia.1252", "WINDOWS-1252"), // Indonesian
	  'is' => array("Islandés", "Íslenska", "is_IS.UTF-8", "Icelandic_Iceland.1252", "WINDOWS-1252"), // Icelandic
	  'it' => array("Italiano", "Italiano", "it_IT.UTF-8", "Italian_Italy.1252", "WINDOWS-1252"), // Italian
	  'iw' => array("Hebreo", "עברית", "he_IL.utf8", "Hebrew_Israel.1255", "WINDOWS-1255"), // Hebrew
	  'ja' => array("Japonés", "日本語", "ja_JP.UTF-8", "Japanese_Japan.932", "CP932"), // Japanese
	  'ji' => array("", "", "", "", ""), // Yiddish
	  'jw' => array("", "", "", "", ""), // Javanese
	  'ka' => array("Georgiano", "ქართული", "ka_GE.UTF-8", "Georgian_Georgia.65001", ""), // Georgian
	  'kk' => array("", "", "", "", ""), // Kazakh
	  'kl' => array("", "", "", "", ""), // Greenlandic
	  'km' => array("", "", "", "", ""), // Cambodian
	  'kn' => array("Kanada", "ಕನ್ನಡ", "kn_IN.UTF-8", "Kannada.65001", ""), // Kannada
	  'ko' => array("Coreano", "조선말", "ko_KR.UTF-8", "Korean_Korea.949", "EUC-KR"), // Korean
	  'ks' => array("", "", "", "", ""), // Kashmiri
	  'ku' => array("", "", "", "", ""), // Kurdish
	  'ky' => array("", "", "", "", ""), // Kirghiz
	  'la' => array("", "", "", "", ""), // Latin
	  'ln' => array("", "", "", "", ""), // Lingala
	  'lo' => array("Laosiano", "ພາສາລາວ", "lo_LA.UTF-8", "Lao_Laos.UTF-8", "WINDOWS-1257"), // Laothian
	  'lt' => array("Lituano", "Lietuvių", "lt_LT.UTF-8", "Lithuanian_Lithuania.1257", "WINDOWS-1257"), // Lithuanian
	  'lv' => array("Letón", "Latviešu", "lat.UTF-8", "Latvian_Latvia.1257", "WINDOWS-1257"), // Latvian/Lettish
	  'mg' => array("", "", "", "", ""), // Malagasy
	  'mi' => array("Maorí", "te Reo Māori", "mi_NZ.UTF-8", "Maori.1252", "WINDOWS-1252"), // Maori
	  'mk' => array("", "", "", "", ""), // Macedonian
	  'ml' => array("Malayalam", "മലയാളം", "ml_IN.UTF-8", "Malayalam_India.x-iscii-ma", "x-iscii-ma"), // Malayalam
	  'mn' => array("Mongol", "Монгол Хэл", "mn.UTF-8", "Cyrillic_Mongolian.1251", ""), // Mongolian
	  'mo' => array("", "", "", "", ""), // Moldavian
	  'mr' => array("", "", "", "", ""), // Marathi
	  'ms' => array("Malayo", "بهاس ملايو", "ms_MY.UTF-8", "Malay_malaysia.1252", "WINDOWS-1252"), // Malay
	  'mt' => array("", "", "", "", ""), // Maltese
	  'my' => array("", "", "", "", ""), // Burmese
	  'na' => array("", "", "", "", ""), // Nauru
	  'ne' => array("", "", "", "", ""), // Nepali
	  'nl' => array("Neerlandés", "Nederlands", "nl_NL.UTF-8", "Dutch_Netherlands.1252", "WINDOWS-1252"), // Dutch
	  'no' => array("Noruego", "Norsk", "no_NO.UTF-8", "Norwegian_Norway.1252", "WINDOWS-1252"), // Norwegian
	  'oc' => array("", "", "", "", ""), // Occitan
	  'om' => array("", "", "", "", ""), // (Afan)/Oromoor/Oriya
	  'pa' => array("", "", "", "", ""), // Punjabi
	  'pl' => array("Polaco", "Polski", "pl.UTF-8", "Polish_Poland.1250", "WINDOWS-1250"), // Polish
	  'ps' => array("", "", "", "", ""), // Pashto/Pushto
	  'pt' => array("Portugués", "Português", "pt_PT.UTF-8", "Portuguese_Portugal.1252", "WINDOWS-1252"), // Portuguese
	  'qu' => array("", "", "", "", ""), // Quechua
	  'rm' => array("", "", "", "", ""), // Rhaeto-Romance
	  'rn' => array("", "", "", "", ""), // Kirundi
	  'ro' => array("Rumano", "Română", "ro_RO.UTF-8", "Romanian_Romania.1250", "WINDOWS-1250"), // Romanian
	  'ru' => array("Ruso", "Русский язык", "ru_RU.UTF-8", "Russian_Russia.1251", "WINDOWS-1251"), // Russian
	  'rw' => array("", "", "", "", ""), // Kinyarwanda
	  'sa' => array("", "", "", "", ""), // Sanskrit
	  'sd' => array("", "", "", "", ""), // Sindhi
	  'sg' => array("", "", "", "", ""), // Sangro
	  'sh' => array("", "", "", "", ""), // Serbo-Croatian
	  'si' => array("", "", "", "", ""), // Singhalese
	  'sk' => array("Eslovaco", "Slovenčina", "sk_SK.UTF-8", "Slovak_Slovakia.1250", "WINDOWS-1250"), // Slovak
	  'sl' => array("Esloveno", "Slovenščina", "sl_SI.UTF-8", "Slovenian_Slovenia.1250", "WINDOWS-1250"), // Slovenian
	  'sm' => array("", "", "", "", ""), // Samoan
	  'sn' => array("", "", "", "", ""), // Shona
	  'so' => array("Somalí", "اللغة الصومالية", "so_SO.UTF-8", "", ""), // Somali
	  'sq' => array("Albano", "Shqip", "sq_AL.UTF-8", "Albanian_Albania.1250", "WINDOWS-1250"), // Albanian
	  'sr' => array("Serbio", "Српски", "sr_CS.UTF-8", "Serbian (Cyrillic)_Serbia and Montenegro.1251", "WINDOWS-1251"), // Serbian
	  'ss' => array("", "", "", "", ""), // Siswati
	  'st' => array("", "", "", "", ""), // Sesotho
	  'su' => array("", "", "", "", ""), // Sundanese
	  'sv' => array("Sueco", "Svenska", "sv_SE.UTF-8", "Swedish_Sweden.1252", "WINDOWS-1252"), // Swedish
	  'sw' => array("", "", "", "", ""), // Swahili
	  'ta' => array("Tamil", "தமிழ்", "ta_IN.UTF-8", "English_Australia.1252", ""), // Tamil
	  'te' => array("", "", "", "", ""), // Tegulu
	  'tg' => array("", "", "", "", ""), // Tajik
	  'th' => array("Tai", "ภาษาไทย", "th_TH.UTF-8", "Thai_Thailand.874", "WINDOWS-874"), // Thai
	  'ti' => array("", "", "", "", ""), // Tigrinya
	  'tk' => array("", "", "", "", ""), // Turkmen
	  'tl' => array("Filipino", "Wikang Tagalog", "tl.UTF-8", "", ""), // Tagalog
	  'tn' => array("", "", "", "", ""), // Setswana
	  'to' => array("Tongano", "chiTonga", "mi_NZ.UTF-8", "Maori.1252", "WINDOWS-1252"), // Tonga
	  'tr' => array("Turco", "Türkçe", "tr_TR.UTF-8", "Turkish_Turkey.1254", "WINDOWS-1254"), // Turkish
	  'ts' => array("", "", "", "", ""), // Tsonga
	  'tt' => array("", "", "", "", ""), // Tatar
	  'tw' => array("", "", "", "", ""), // Twi
	  'uk' => array("Ucraniano", "Українська", "uk_UA.UTF-8", "Ukrainian_Ukraine.1251", "WINDOWS-1251"), // Ukrainian
	  'ur' => array("", "", "", "", ""), // Urdu
	  'uz' => array("", "", "", "", ""), // Uzbek
	  'vi' => array("Vietnamita", "Tiếng việt", "vi_VN.UTF-8", "Vietnamese_Viet Nam.1258", "WINDOWS-1258"), // Vietnamese
	  'vo' => array("", "", "", "", ""), // Volapuk
	  'wo' => array("", "", "", "", ""), // Wolof
	  'xh' => array("", "", "", "", ""), // Xhosa
	  'yo' => array("", "", "", "", ""), // Yoruba
	  'zh' => array("Chino", "汉语", "zh_CN.UTF-8", "Chinese_China.936", "CP936"), // Chinese
	  'zu' => array("", "", "", "", "") // Zulu
		
	);
	
	public function __construct($options=array()) {
		
		$this->default = $this->setVar($options, "default", "string");
		$this->allowed = $this->setVar($options, "allowed", "array");
		
		return $this->setLang();
		
	}
	
	private function setVar($options, $option, $type="string") {
		
		if (is_array($options)) {
			if (isset($options[$option]) && gettype($options[$option]) == $type) {
				return $options[$option];
			}
		}
		
		return $this->$option;
		
	}
	
	public function getLang() {
		
		$lang = $this->default;
		
		if (isset($_SESSION['lang'])) {
			$lang = inputClean::clean($_SESSION['lang']);
			if (!$this->checkLang($lang)) {
				$lang = $this->default;
			}
		}
		
		if (isset($_REQUEST['lang'])) {
			$new_lang = inputClean::clean($_REQUEST['lang'], 2);
			if ($this->checkLang($new_lang)) {
				$lang = $new_lang;
			}
		}
		
		return $lang;
		
	}
	
	private function checkLang($lang) {
		
		if (isset($this->locales[$lang]) 
				&& (!empty($this->allowed) && in_array($lang, $this->allowed))) {
			return true;
		} else {
			return false;
		}
		
	}
	
	public function setLang() {
		
		$lang = $this->getLang();
		
		$_SESSION['lang'] = $lang;
		
		$this->lang = $lang;
		
		putenv("LANG=" . $this->locales[$lang][2]); // Sets the LANG environment variable and instructs gettext which locale it will be using for this session
		$locale = setlocale(LC_ALL, $this->locales[$lang][2], $this->locales[$lang][3], "esp"); // Specifies the locale used in the application and affects how PHP sorts strings, understands date and time formatting, and formats numeric values
		$domain = "texts"; // Refers to the catalog file used to store the translation
		bindtextdomain($domain, "./locale"); // Function tells gettext where to find the domain to use; the first parameter is the catalog name without the .mo extension, and the second parameter is the path to the parent directory
		bind_textdomain_codeset($domain, "UTF-8"); // Sets in which encoding will the messages from domain be returned by gettext() and similar functions
		
		textdomain($domain); // Sets the domain to search within when calls are made to gettext()
		
		$this->numeric = localeconv();
		
		return true;
		
	}
	
	public function getLangName($lang, $proper=false) {
		
		if (!isset($this->locales[$lang])) {
			return false;
		}
		
		return ($proper) ? $this->locales[$lang][1] : $this->locales[$lang][0];
		
	}
	
}

?>