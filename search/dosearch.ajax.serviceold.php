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
require_once("../global.settings.php");

require_once("../libs/search.lib.php");
require_once("../libs/graph.lib.php");
require_once("../libs/ontology.lib.php");
//require_once("../libs/sparql.queries.lib.php");

//require_once("query.handling.common.php");

function formatString($str){
    return stripOntologyNamespace($str);
}

$query = $_GET['q'];
$label = $_GET['label'];
//$sparql = getSPARQLEngine();
echoN("<b> $label </b>");
//$result = getRelatedHadeeth($sparql, $query);
global $modern_treatment_class_name, $prophet_treatment_class_name, $modern_material_class_name, $prophet_material_class_name;
$treatment_prophet = getIllnessTreatmentFromCache( $query, $prophet_treatment_class_name);
$treatmment_modern = getIllnessTreatmentFromCache( $query, $modern_treatment_class_name);
$material_prophet = getIllnessMaterialFromCache( $query, $prophet_material_class_name);
$material_modern = getIllnessMaterialFromCache( $query, $modern_material_class_name);

//formating

$str ="<b>Prophet's treatment:</b> \n";
foreach ($treatment_prophet as $row){
    $str = "$str \n<br> ". formatString($row->treatment);
}
$str = "$str \n<br> <br> <b>Modern treatment:</b> \n";

foreach ($treatmment_modern as $row){
    $str = "$str \n<br>".formatString( $row->treatment );
}
$str = "$str \n<br> <br> <b>Prophet's material used:</b> \n";

foreach ($material_prophet as $row){
    $str = "$str \n<br> ".formatString($row->treatment);
    if( isset($row->text))
         $str = "$str \n<br> ".formatString($row->text);
}
$str = "$str \n<br> <br> <b>Modern material used:</b> \n";

foreach ($material_modern as $row){
    $str = "$str \n<br> ".formatString($row->treatment) ;
    if( isset($row->text))
        $str = "$str \n<br> ".formatString($row->text);

}
echo $str;



?>






