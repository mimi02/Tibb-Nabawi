<?php 
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================
$MAIN_ROOT_PATH = dirname(__FILE__)."/";
//apc_cache_clear();
error_reporting(E_ALL);
ini_set('display_errors', 1);


$BUILD_VERSION = "0.1.1";
$HOME_PATH="teb/";

$SERVER_NAME = "http://localhost/teb";// "http://localhost/teb";//"http://3.122.133.65";

$MAIN_JS_PATH = $SERVER_NAME."/libs/js/main.js?bv=$BUILD_VERSION";


$JQUERY_PATH = $SERVER_NAME."/libs/js/jquery/jquery-2.1.1.min.js";

$D3_PATH = $SERVER_NAME."/libs/js/d3/d3.js";

$JQUERY_TAGCLOUD_PATH = $SERVER_NAME."/libs/js/jquery.tagcloud.js";
$QE_STYLE_PATH = $SERVER_NAME."/qe.style.css";
$TINYSORT_PATH = $SERVER_NAME."/libs/js/tinysort/tinysort.min.js";
$TINYSORT_JQ_PATH = $SERVER_NAME."/libs/js/tinysort/jquery.tinysort.min.js";


mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");


$qaOntologyFile =  $MAIN_ROOT_PATH."data/ontology/Tibb_Nabawi_09_10_rdf.owl";
$qaOntologyNamespace ="http://www.semanticweb.org/muna/ontologies/2018/6/TibbNabawi-ontology-15#";
// "http://qurananalysis.com/data/ontology/qa.ontology.v1.owl#";
$illness_class_name = "medical_condition";
$treatment_class_name="Treatment";
$material_class_name="material";
$modern_treatment_class_name="ModernMedicicalTreatment";
$modern_material_class_name="modern_time_material";
$prophet_treatment_class_name="Prophet_timeTreatment";
$prophet_material_class_name="prophet_time_material";
$is_strongly_verified = "isStronglyVerifiedBy";
$is_weakly_verified="isWeaklyVerifiedBy";
//$englishResourceFile = dirname(__FILE__)."/data/resources.en";
//$arabicResourceFile = dirname(__FILE__)."/data/resources.ar";
$sparql_endpoint=/*$SERVER_NAME.*/'http://localhost:3030/tibb_10/sparql';

$apcMemoryOntologyKey = "MODEL_TEBB_ONTOLOGY";

$modelSources = array();
$supportedLanguages = array("EN","AR");
		

//$modelSources['AR']= array("type"=>"TXT","file"=>$quranFileAR);
//$modelSources['AR_UTH']= array("type"=>"TXT","file"=>$quranFileUthmaniAR);
////$modelSources['AR']= array("type"=>"XML","file"=>$quranFileAR_XML);
//$modelSources['EN']= array("type"=>"TXT","file"=>$quranFileEN);

//$serializedModelFile = dirname(__FILE__)."/data/model.ser";
//
//$pauseMarksFile = dirname(__FILE__)."/data/pause.marks";
//$arabicStopWordsFile = dirname(__FILE__)."/data/quran-stop-words.strict.l1.ar";
//$arabicStopWordsFileL2 = dirname(__FILE__)."/data/quran-stop-words.strict.l2.ar";
//$englishStopWordsFile = dirname(__FILE__)."/data/english-stop-words.en";

//$sajdahMark = "۩";
//$saktaLatifaMark = "ۜ";
//$numberOfVerses = 6236;
//$numberOfSuras = 114;
//$basmalaText = "بسم الله الرحمن الرحيم";
//$basmalaTextUthmani = "بِّسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ";
//$basmalaTextUthmani2 = "بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ";
//$mandatoryStop = "ۘ";


## LOCATION SIGNIFICANT ##
require_once($MAIN_ROOT_PATH."/libs/core.lib.php");
//require_once($MAIN_ROOT_PATH."/model.loader.php");



if ( isDevEnviroment() )
{
	error_reporting(E_ERROR);
	ini_set('display_errors', true);
	function shutdown()
	{
	
		$isSevereError = false;
		$errorArr = error_get_last();
		
		if (!empty($errorArr) )
		{
				
			switch($errorArr['type'])
			{
				case E_ERROR:
					//case E_USER_ERROR:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
					$isSevereError = true;
					break;
				default:
					
	
			}
		}
	
		if ($isSevereError)
		{
			echo "SEVERE ERROR: ".$errorArr['message'];
			preprint_r($errorArr);
		}
	}
	
	register_shutdown_function('shutdown');
}
?>

<script type="text/javascript">
    var SERVER_NAME = '<?php echo $SERVER_NAME ?>' ;    
</script>
