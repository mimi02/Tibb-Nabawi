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
require_once("../libs/sparql.queries.lib.php");

require_once("../libs/search.lib.php");
require_once("../libs/graph.lib.php");

$sparql = getSPARQLEngine();// apc_fetch("sparql");
$direction = "rtl";
$lang = '';
if(isset($_GET['lang']))
        $lang = $_GET['lang'];
if ( empty($lang))
{
	$lang = "EN";
	$direction = "ltr";
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Explore Tib Annabwi </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Exploratory search for the Tibb Ontology, Explore Al Tib Annabwi by Illnesses">
    <meta name="author" content="">

	<script type="text/javascript" src="<?="".$JQUERY_PATH?>" ></script>
	<script type="text/javascript" src="<?="".$MAIN_JS_PATH?>"></script>
	<script type="text/javascript" src="<?="".$D3_PATH?>"></script>
	
	<link rel="stylesheet" href="../qe.style.css?bv=<?=$BUILD_VERSION?>" />
	<link rel="icon" type="image/png" href="../favicon.png">
	<script type="text/javascript">
	</script>
     
     <style>

</style>
       
  </head>
  <body>
		<?php 
			require("../header.php");
	
		?>

  <div id='main-container'>
			 <div id='options-area'>
			 			  	<?php 
						  		include_once("../header.menu.php");
						  	?>
			  		</div>
			  		
			  		
			<div id='explore-lang-select-area'>
			
			  	<select id='language-selection' onchange="handlePresentationOptions()" style='float:left'>
	   				<option value='EN' <?php if ($lang=="EN") echo 'selected'?> >EN</option>
	   				<option value='AR' <?php if ($lang=="AR") echo 'selected'?>>AR</option>
	   			</select>	  		
	   			<span id='explore-guide-msg' style='float:none'>
			  	&nbsp;Click on any medical condition to compare treatments and materials used in prophet an modern time.
			    </span>
			  
		    </div>
<?php 

function formatResultEn($name){
    
        return substr(strchr($name, "#") ,1);
    
}

function formatResultAr($name){
    if ( !isSimpleQuranWord($name)){
             $name = convertUthamniQueryToSimple($name);
    }
       $name = cleanAndTrim($name);
       return $name;
}

//$result = getAllIllnessesFromCache();
$result = getAllIllnesses($sparql);
//print_r($result->{'owl_data'}{'annotations'});
$clusteredArrJSON =  array();
foreach ($result as $row) {
    $str =  formatResultEn($row->word);
    $ar_str =  formatResultAr($row->ar_word);
    $clusteredArrJSON[] = (object)array("word" => $str, "ar_word" => $ar_str);
  
}
$clusteredArrJSON = json_encode($clusteredArrJSON);

?>
		  		
		  
		  <div id='exploration-area'>

			

			</div>
					

   </div>
   

	<script type="text/javascript">

				
		$(document).ready(function()
		{

			$("#options-area").attr("class","oa-explore");

			var isForeignObjectSupported = document.implementation.hasFeature('http://www.w3.org/TR/SVG11/feature#Extensibility','1.1');

			if ( !isForeignObjectSupported )
			{
				showBrowserSupportErrorMessage('exploration-area');
			}
		
		});

		var jsonData = <?=$clusteredArrJSON?>;
		
//                alert(jsonData[0] /*JSON.stringify(jsonData)*/);

		var width = ($(document).width()-500),
		    height = 700,
		    padding = 1.5, // separation between same-color circles
		    clusterPadding = 6, // separation between different-color circles
		    maxRadius = -1;

		var n = jsonData.length, // total number of circles
		    m = n; // number of distinct clusters -> we have only 1 cluster
		var clusters = new Array();
		var clustersSizes = new Array();

		//alert(jsonData.length);
		var clusterId = 0;
                //categorize clusters ->. we have only only 1 cluster
/*		jsonData.forEach(function(d)
				{
					clusterId = d.cluster; 
					if ( clustersSizes[clusterId]==null)
					{
						clustersSizes[clusterId]=0;
					}
					clustersSizes[clusterId]++;
					
					if ( d.radius > maxRadius)
					{
						maxRadius = d.radius;
						
					}
					if ( !clusters[clusterId] || d.radius > clusters[clusterId].radius )
					{
						clusters[clusterId] = d;
					}
				}
		);*/

		//alert(JSON.stringify(clustersSizes));

		var clusterVerticalLocationFactor=100;
                var clusterNodesCount = jsonData.length;
		// set cluster nodes to random locations and set thier children to the same
		jsonData.forEach(function(d)
				{

                        //clusterId = d.cluster; 
//			clusterNode = d/*clusters[clusterId]*/;
                            
                            clusterXLocation =(clusterNodesCount)%4;

                            if ( clusterXLocation > width )
                            {
                                    clusterXLocation = width/2;
                            }

                            clusterYLocation = (parseInt(clusterNodesCount)*5);
                            if ( clusterYLocation > height )
                            {

                                    clusterYLocation = height -400;
                            }

                            //alert(clusterYLocation);

//                            d.x = clusterXLocation;
//                            d.y =  clusterYLocation;
                        d.radius =50;
                        d.cluster = 1;//color
			d.x = clusterXLocation+  (multiplyByRandomSign(Math.random()) )*450;
			d.y =  clusterYLocation+ (multiplyByRandomSign(Math.random()) )*200;

                            clusterVerticalLocationFactor++;
                    }
		);


		var color = d3.scale.category10().domain(d3.range(n));

		//alert(JSON.stringify(jsonData));
		
		var force = d3.layout.force()
		    .nodes(jsonData)
		    .size([width, height])
		    .gravity(0.03)
		    .charge(0)
		    .friction(0.2)
		    .on("tick", tick)
		    .on("end", handleEndEvent)
		   	.on("start", handleStartEvent);
//	    


		var svg = d3.select("#exploration-area").append("svg")
		    .attr("width", width)
		    .attr("height", height)
		    .attr("xlink","http://www.w3.org/1999/xlink");


		function getAdjustedCornerPointX(currentPageX)
		{
                    var layerWidth = 400;

//			alert(currentPageX+"+"+layerWidth +">"+ width);
			var finalX = 0;
			
			
			if ( currentPageX+layerWidth > width )
			{
				
				finalX =  currentPageX-(layerWidth);
			}

				if ( finalX < 0 )
				{
					finalX = 50;
				}

			return finalX;
		}
		
		function getAdjustedCornerPointY(currentPageY)
		{
			var layerHeight= 400;
//                        alert(currentPageY+"+"+layerHeight +">"+ height);
			if ( currentPageY+layerHeight > height )
			{
				return (height-currentPageY)/2;
			}

			return currentPageY;
		}
		
		var circle = svg.selectAll("circle")
		    .data(jsonData)
		  .enter().append("g")
		  .attr("class","explore-node")
		  .on("click",function(d) //onclick search with this concept to show related hadeeths
		  {

			   svg.selectAll("#explore-result-verses-container").remove();
                                
				var word = d.word;
                                var label = "<?=$lang?>" == "EN" ? d.word : d.ar_word;
				var foreignObject = svg.append("foreignObject")
				.attr("id","explore-result-verses-container")
				.attr("width","400px")
				.attr("height","400px")
				.attr("x", /*(d3.event.pageX-100)*/ getAdjustedCornerPointX(d3.event.pageX-50) + "px")
				.attr("y",/*(d3.event.pageY-100)*/ getAdjustedCornerPointY(d3.event.pageY-100) + "px");

				
				var body = foreignObject.append("xhtml:body");
	
                            body.append("xhtml:img")
			    .attr("src", SERVER_NAME+"/images/close-icon-black.png")
			    .attr("class","explore-verses-close")
			    .on("click", function() {
					
					$("#explore-result-verses-container").css("display","none");

					 svg.selectAll("#explore-result-verses-container").remove();
				});

				body.append("xhtml:div")
				.attr("id","explore-result-verses")
				//.attr("xmlns","http://www.w3.org/1999/xhtml")
				.html("");
		        	// one concept search
                                
//		        	word = "CONCEPTSEARCH:"+word+"";
                                //update this method to load hadeeth
				showResultsForQueryInSpecificDiv(word, label,"true", "explore-result-verses", "<?=$lang?>");

				
				
		  });

		circle.append("circle")
		    .attr("r", function(d) { return d.radius; })
		    .style("fill", function(d) { return color(d.cluster); })
		    .attr("cx", function(d) { return d.x; })
		    .attr("cy", function(d) { return d.y; })
		    .call(force.drag);

		circle.append("text")
//             .attr("x", function(d) { return d.x; })
//	.attr("y", function(d) { return d.y; })            
                 .text(
                function(d) 
                {                   
                        var word =  "<?=$lang?>" == "EN" ? d.word : d.ar_word;//text to show

                        if ( word!=undefined && word.length > 10 )
                        {
                                word = word.substring(0,10)+"..";
                        }

                        return word; 


                } );

		circle.append("title").text( function(d) { return ("<?=$lang?>" == "EN" ? d.word : d.ar_word); } );

		function tick(e) {

			
                circle.each(cluster(10 * e.alpha * e.alpha)).each(collide(0.5));

		   circle.each(handleOutOfBoundry(e.alpha));

	      //if ( e.alpha < 0.05 ) force.stop();
	      
		  circle.select("circle").attr("cx", function(d) { return d.x; });
		     circle.select("circle").attr("cy", function(d) { return d.y; });

		  circle.select("text").attr("dx", function(d) { return d.x; });
		     circle.select("text").attr("dy", function(d) { return d.y; });

		  
		}

		force.start();

		function handleEndEvent()
		{
			//circle.each(handleOutOfBoundry(0.5));

			//force.resume();
			
		}
		function handleStartEvent()
		{
			//circle.each(handleOutOfBoundry(0.5));
		
		}
		 
	
		function handleOutOfBoundry(alpha)
		{

			 var boundryPadding = 100;
			 return function(d) 
			 {
				
				 
				if ( (d.x-d.radius) < boundryPadding || (d.x+d.radius) > width-boundryPadding || 
						 (d.y-d.radius) < boundryPadding || (d.y+d.radius) > height-boundryPadding)
				{

					//alert(JSON.stringify(d)+" "+d.x+" "+d.y+" "+d.radius);
					
					dsClusterObj = clusters[d.cluster];
					targetXPos = d.x;
					targetYPos = d.y;

					if ( targetXPos > width ||  targetXPos <0  )
					{

						
						diff = Math.abs(width-targetXPos)/2;

						if ( targetXPos < 0 )
						{
							targetXPos +=diff;
						}
						else
						{
							targetXPos -=diff;
						}
						

						d.x = targetXPos;
					
						
					}

					if ( targetYPos  > height  ||  targetYPos <0   )
					{
						diff = Math.abs(  height -targetYPos )/2;

						if ( targetYPos < 0 )
						{
							targetYPos +=diff;
						}
						else
						{
							targetYPos -=diff;
						}
						
						d.y = targetYPos;
					}
			
//					d.x += (targetXPos-d.x)*(alpha);
//					d.y += (targetYPos-d.y)*alpha;
				}
	
			 }
		}

		function preTickClustering()
		{
			circle
		      .each(cluster(10 * e.alpha * e.alpha))
		      .each(collide(0.5));
		}
		

		

		// Move d to be adjacent to the cluster node.
		function cluster(alpha) {
		  return function(d) {
                      return;
//		   var cluster = clusters[d.cluster];
//		    if (cluster === d) return;
//		    var x = d.x - cluster.x,
//		        y = d.y - cluster.y,
//		        l = Math.sqrt(x * x + y * y),
//		        r = d.radius + cluster.radius;
//		    if (l != r) {
//		      l = (l - r) / l * alpha;
//		      d.x -= x *= l;
//		      d.y -= y *= l;
//		      cluster.x += x;
//		      cluster.y += y;
//		    }
		  };
		}

		// Resolves collisions between d and all other circles.
		function collide(alpha) {
		  var quadtree = d3.geom.quadtree(jsonData);
		  return function(d) {
		    var r = d.radius + maxRadius + Math.max(padding, clusterPadding),
		        nx1 = d.x - r,
		        nx2 = d.x + r,
		        ny1 = d.y - r,
		        ny2 = d.y + r;
		    quadtree.visit(function(quad, x1, y1, x2, y2) {
		      if (quad.point && (quad.point !== d)) {
		        var x = d.x - quad.point.x,
		            y = d.y - quad.point.y,
		            l = Math.sqrt(x * x + y * y),
		            r = d.radius + quad.point.radius + (d.cluster === quad.point.cluster ? padding : clusterPadding);
		        if (l < r) {
		          l = (l - r) / l * alpha;
		          d.x -= x *= l;
		          d.y -= y *= l;
		          quad.point.x += x;
		          quad.point.y += y;
		        }
		      }
		      return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
		    });
		  };
		}
		
	
		function handlePresentationOptions()
		{
			
			
			var selectedLang = $("SELECT[id=language-selection] option:selected").val();
			var newURL ="";
			if ( location.href.indexOf("?") >= 0 )
			{
				newURL=location.href.substring(0,location.href.indexOf("?"));
			}
			
				newURL=newURL+"?lang="+selectedLang;

				location.href = newURL;
			
		}
	</script>
		

	<?php 
		require("../footer.php");
	?>
	

  </body>
</html>
