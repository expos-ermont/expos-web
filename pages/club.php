<?php
/**
 * Display informations about the association
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');

/*
Google Maps activation key for expos-ermont.com : ABQIAAAA1464aNC0FEMIR-kxU44MUxTfvuWSyVlBkpFZBWaGxGgw3hZkNRTTyll8ySOdGMOTio9RATaWjO55sA
*/

$content = '
	<h2 id="inscription">S\'inscrire</h2>
	<h3>Cotisation</h3>
	<table class="fullWidth">
		<tr>
			<th>Catégorie</th>
			<th>
				Tarifs 2015/2016<br />
				(Cotisation club + License fédérale)
			</th>
		</tr>
		<tr>
			<td>Senior</td>
			<td><b>190,00€</b></td>
		</tr>
		<tr>
			<td>Feminin senior</td>
			<td><b>160,00€</b></td>
		</tr>
		<tr>
			<td>13 à 18 ans (1998 à 2003)</td>
			<td><b>160,00€</b></td>
		</tr>
		<tr>
			<td>12 ans et moins &lt;2003</td>
			<td><b>140,00€</b></td>
		</tr>
		<tr>
			<td>
				Famille nombreuse (même foyer)<br />
				2ème personne<br>
				3ème personne et +
			</td>
			<td>
				<br>
				-25,00€<br>
				-50,00€
			</td>
		</tr>
	</table>

	N.B.: les tarifs annoncés intègrent la licence pour la saison 2015/2016 et l\'assurance sportive. Cette dernière peut être remboursée sur demande. L\'assureur fédéral propose également une assurance complémentaire (se renseigner auprès des responsables du club).<br />
	Règlement en espèces ou par chèque à l\'ordre des Expos d\'Ermont, possibilité de payer en 3 fois.<br />
	<h3>Pièces à fournir</h3>
	Le dossier d\'inscription rempli,<br />
	Le certificat médical (de préférence médecin du sport), avec surclassement pour tous les enfants, quelque soit la catégorie,
	Autorisation parentale pour les mineurs.
	<h3>Notes de sécurité</h3>
	Pour votre sécurité, nous vous demandons de porter une coquille pendant les entrainements et les matchs. L\'assurance ne fonctionne que si la coquille est portée.<br />
	PAS DE COQUILLE = PAS D\'ENTRAINEMENT = PAS DE MATCH<br />
	Le club met des gants à votre disposition pendant les entrainements et les matchs. Vous pouvez aussi consulter nos différents catalogues de vente par correspondance<br />
	<br />
	<a href="'.$_CONF['wwwRoot'].'data/dossier_inscription_2015-2016.pdf" title="Dossier inscription 2014/2015" target="_blank">Dossier d\'incription</a><br />
	<a href="'.$_CONF['wwwRoot'].'data/dossier_renseignement_2015-2016.pdf" title="Dossier renseignement 2014/2015" target="_blank">Dossier de renseignement</a><br />
	<a href="'.$_CONF['wwwRoot'].'data/manuel_baseball.pdf" title="Manuel de baseball" target="_blank">Manuel sur le baseball</a>

	<h2 id="sections">Les sections</h2>
	<table class="fullWidth">
		<tr>
			<th>Catégorie</th>
			<th>Année de naissance</th>
			<th>Horaires d\'entrainement</th>
		</tr>
		<tr>
			<td>U9</td>
			<td>
				2007-2009
			</td>
			<td>
				Samedi de 14h à 16h *
			</td>
		</tr>
		<tr>
			<td>U12</td>
			<td>
				2004-2006
			</td>
			<td>
				Samedi de 14h à 16h *
			</td>
		</tr>
		<tr>
			<td>U15</td>
			<td>
				2001-2003
			</td>
			<td>
				Samedi de 14h à 16h *
			</td>
		</tr>
		<tr>
			<td>U18-Séniors Baseball</td>
			<td>Avant 2001</td>
			<td>
				Mardi de 20h à 22h<br />
				Jeudi de 20h à 22h **
			</td>
		</tr>
		<tr>
			<td>Softball mixte</td>
			<td>Avant 2001</td>
			<td>
				<i>Mardi de 20h à 22h</i> ***<br />
				Mercredi de 20h à 22h
			</td>
		</tr>
		<tr>
			<td>Softball féminin</td>
			<td>Avant 2001</td>
			<td>
				<i>Mardi de 20h à 22h</i> ***<br />
				Mercredi de 20h à 22h<br>
				Samedi de 13h à 15h
			</td>
		</tr>
	</table>
	<p>
		(*) Les horaires du samedi sont susceptibles de changer selon le nombre d\'adhérents et des jours de matchs.<br>
		(**) Créneau au gymnase (CDFAS) en période hivernale (décembre-février)<br>
		(***) Créneau <strong>uniquement</strong> en période hivernale au gymnase (novembre-février)
	</p>

	<h2 id="adresses">Les bonnes adresses</h2>
	<ul>
		<li>Siege Social: Service des sports de la mairie d\'ermont, 100 rue Louis Savoie, 95120 Ermont</li>
		<li>Terrain: Complexe Sportif Gaston Rebuffat, 1 rue Jean de Florette, 95120 Ermont</li>
		<li>Gymnase Senior: CDFAS, 64 rue des Bouquinvilles, 95600 Eaubonne</li>
		<li>Gymnase Jeune: Complexe Sportif Gaston Rebuffat, 1 rue Jean de Florette, 95120 Ermont</li>
	</ul>
	<br />
	<div id="map"></div>
	<script type="text/javascript">
		var map;
		function initMap() {
			var myLatLng = {lat: 48.987012, lng: 2.243984};

			map = new google.maps.Map(document.getElementById("map"), {
				center: myLatLng,
    		zoom: 15,
				mapTypeControl: true,
		    mapTypeControlOptions: {
		      style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
		      mapTypeIds: [
		        google.maps.MapTypeId.ROADMAP,
		        google.maps.MapTypeId.TERRAIN
		      ]
		    },
		    zoomControl: true,
		    zoomControlOptions: {
		      style: google.maps.ZoomControlStyle.SMALL
		    }
			});

			var image = "'.$_CONF['wwwRoot'].'picts/logo_expos_mini.png";
		  var marker = new google.maps.Marker({
		    position: myLatLng,
		    map: map,
		    icon: image
		  });
		}
	</script>
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCE3K2m02-rBVOUOCd-H3PWsxsnOVQxzvM&callback=initMap" type="text/javascript"></script>
';

$page = new Page();
$page->title = 'Le club des Expos';
$page->add('content' , $content);
$page->send();
?>
