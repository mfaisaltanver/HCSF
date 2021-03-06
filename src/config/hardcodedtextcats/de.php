<?php
return [
    'fullstop' => '.',
    'changescommitted' => 'Änderungen gespeichert:',

    'site_currently_in_maintenance_mode' => 'Diese Website ist zur Zeit im Wartungsmodus, bitte versuchen Sie es später noch einmal.',

    'admin_nav_home' => 'Admin Home',
    'admin_nav_orders' => 'Bestellungen',
    'admin_nav_items' => 'Artikel',
    'admin_nav_itemgroups' => 'Artikelgruppen',
    'admin_nav_customers' => 'Kunden',
    'admin_nav_pages' => 'Seiten',
    'admin_nav_textcats' => 'Textkataloge',
    'admin_nav_cleartemplatecache' => 'Templatecache leeren',
    'admin_nav_clearimagecache' => 'Bildercache leeren',
    'admin_nav_phpinfo' => 'PHPInfo',
    'admin_nav_dbstatus' => 'DB Status',

    'itemadmin_search_title' => 'Artikelsuche',
    'itemadmin_search_searchstring' => 'Suche nach:',
    'itemadmin_search_searchfield' => 'Suche in:',
    'itemadmin_search_searchorder' => 'Sortiere nach:',
    'itemadmin_search_submit' => 'Suchen',

    'itemadmin_msg_nomatches' => 'Die Artikelsuche hat keine Ergebnisse geliefert.',
    'itemadmin_msg_displaying_1' => 'Zeige',
    'itemadmin_msg_displaying_2' => 'Artikel.',
    'itemadmin_msg_notext' => 'In dieser Sprache sind noch keine Texte angelegt!',
    'itemadmin_msg_notext_add' => '[Anlegen]',
    'itemadmin_msg_itemnotooshort' => 'Bitte verwenden Sie mindestens 4 Zeichen für die Artikelnummer.',
    'itemadmin_msg_itemnoalreadytaken' => 'Diese Artikelnummer ist bereits vergeben.',

    'itemadmin_base_title' => 'Sprachunabhängig',
    'itemadmin_base_itemno' => 'Artikelnr:',
    'itemadmin_base_itemname' => 'Artikelname:',
    'itemadmin_base_itemname_tooltip' => 'Der Basisname des Artikels',
    'itemadmin_base_img' => 'Artikelbild:',
    'itemadmin_base_img_tooltip' => 'Der Dateiname des Artikelbildes',
    'itemadmin_base_price' => 'Preis:',
    'itemadmin_base_price_tooltip' => 'Der Preis des Artikels, Dezimaltrenner ist der&#xa;Punkt (9.99), bitte kein Komma verwenden!',
    'itemadmin_base_vat' => 'MwSt:',
    'itemadmin_base_vat_tooltip' => 'Der Mehrwertsteuersatz des Artikels',
    'itemadmin_base_rg' => 'RG:',
    'itemadmin_base_rg_tooltip' => 'Die Rabattgruppe des Artikels',
    'itemadmin_base_weight' => 'Gewicht:',
    'itemadmin_base_weight_tooltip' => 'Das Artikelgewicht in Gramm',
    'itemadmin_base_index' => 'Artikelindex:',
    'itemadmin_base_index_tooltip' => 'Der Artikelindex, mehrere mit einer Pipe | trennen: A010|A020|A030',
    'itemadmin_base_prio' => 'Prio:',
    'itemadmin_base_prio_tooltip' => 'Die Priorität für die Sortierung der Artikel in der Liste.&#xa;Numerisch, 3 Ziffern, je höher, desto weiter unten.',
    'itemadmin_base_group' => 'Artikelgruppe:',
    'itemadmin_base_group_tooltip' => 'Die Artikelgruppe für die gruppierte&#xa;Listung, siehe Artikelgruppenverwaltung.',
    'itemadmin_base_data' => 'Zusatzdaten (JSON):',
    'itemadmin_base_data_tooltip' => 'Zusätzliche Artikelkonfiguration, konfigurierbar.&#xa;Die Standardkonfiguration beinhaltet:&#xa;{&#xa;    &#34;suggestions&#34;:&#34;0011|0012|0001|0002&#34;,&#xa;    &#34;sale&#34;: {&#xa;        &#34;price&#34;:79.99,&#xa;        &#34;start&#34;:&#34;20140809&#34;,&#xa;        &#34;end&#34;:&#34;20140826&#34;&#xa;    },&#xa;    &#34;detailimg&#34;:[&#34;image1.jpg&#34;,&#34;image2.jpg&#34;],&#xa;    &#34;soldout&#34;:false&#xa;}&#xa;- Wenn soldout auf true gesetzt wird, wird der Artikel gelistet, ist aber nicht bestellbar.',

    'itemadmin_lang_title' => 'Sprachspezifisch',
    'itemadmin_lang_nameoverride' => 'Sprachspezifischer Name',
    'itemadmin_lang_nameoverride_tooltip' => 'Wird hier ein Name gesetzt, so wird der Basisname in der aktuellen Sprache des Artikels durch diesen ersetzt.',
    'itemadmin_lang_text1' => 'Text 1:',
    'itemadmin_lang_text1_tooltip' => 'Der Haupttext des Artikels, HTML Quelltext, unsichere Tags werden herausgefiltert.',
    'itemadmin_lang_text2' => 'Text 2:',
    'itemadmin_lang_text2_tooltip' => 'Der sekundäre Artikeltext, HTML Quelltext, unsichere Tags werden herausgefiltert.',

    'itemadmin_submit' => 'Speichern',
    'itemadmin_linktoitemdetail' => 'Zur Artikeldetailseite (neues Fenster)',

    'itemadmin_add_itemno' => 'Artikelnummer',
    'itemadmin_add_submit' => 'Artikel anlegen',
    'itemadmin_add_link' => '[Artikel hinzufügen]',

    'itemadmin_search_itemno' => 'Artikelnummer',
    'itemadmin_search_itemname' => 'Artikelname',
    'itemadmin_search_itemindex' => 'Artikelindex',

    'itemadmin_list_active' => 'Aktiv',
    'itemadmin_list_itemno' => 'Art. Nr.',
    'itemadmin_list_name' => 'Name',
    'itemadmin_list_edit' => 'bearb.',

    'pageadmin_choose_pagegroup' => 'Seitengruppe:',
    'pageadmin_choose_page' => 'Bitte wählen Sie die zu bearbeitende Seite aus:',
    'pageadmin_choose_pagegroup_first' => 'Bitte wählen Sie zuerst eine Seitengruppe aus.',
    'pageadmin_choose_submit' => 'Weiter',
    'pageadmin_choose_add' => '[Seite hinzufügen]',

    'pageadmin_add_pagekey' => 'Seitenschlüssel:',
    'pageadmin_add_pagekey_tooltip' => 'Bitte geben Sie den kompletten Pfad an,&#xa;z.B.: /thema/artikel.html&#xa;Wenn als Dateiname index.html verwendet wird,&#xa;dann wird diese Seite automatisch geladen,&#xa;wenn beim Seitenaufruf nur ein Verzeichnis&#xa;angegeben wird, z.B.: /thema/',
    'pageadmin_add_submit' => 'Anlegen',
    'pageadmin_add_error_reservedpath' => 'Der von Ihnen gewählte Schlüssel ist für das System reserviert, er darf nicht mit "/_" beginnen.',
    'pageadmin_add_error_keytooshort' => 'Bitte verwenden Sie mindestens 4 Zeichen für den Seitenschlüssel.',
    'pageadmin_add_error_keyalreadyinuse' => 'Dieser Seitenschlüssel ist bereits vergeben.',

    'pageadmin_edit_currentpage' => 'Aktuelle Seite:',
    'pageadmin_edit_languagespecific' => 'Sprachspezifisch',
    'pageadmin_edit_pagecontent' => 'Seiteninhalt',
    'pageadmin_edit_pagecontent_tooltip' => 'HTML Quelltext, unsichere Tags werden herausgefiltert.',
    'pageadmin_edit_pagetitle' => 'Seitentitel:',
    'pageadmin_edit_pagetitle_tooltip' => 'Reiner Text, erscheint im Browser als Titel der Seite.&#xa;Wenn dieses Feld leer gelassen wird, wird der&#xa;Standardseitentitel verwendet.',
    'pageadmin_edit_metadescription' => 'Meta-Description:',
    'pageadmin_edit_metadescription_tooltip' => 'Reiner Text, eine kurze Zusammenfassung der Seite.',
    'pageadmin_edit_metakeywords' => 'Meta-Keywords:',
    'pageadmin_edit_metakeywords_tooltip' => 'Reiner Text, Schlüsselwörter, durch die diese&#xa;Seite bei Suchmaschinen gefunden werden soll.',
    'pagedamin_edit_nolanguagespecificdataexists' => 'In dieser Sprache sind noch keine Texte angelegt. Klicken Sie hier um dies zu tun',
    'pageadmin_edit_basedata' => 'Sprachunabhängig',
    'pageadmin_edit_pagetype' => 'Page type:',
    'pageadmin_edit_pagetype_tooltip' => 'Welche Seitenvorlage soll verwendet werden?&#xa;content: standardlayout&#xa;contentnosubnav: Standardlayout ohne Subnavigation&#xa;itemoverview: Artikelübersichtsseite&#xa;itemoverviewgrpd: Artikelübersichtsseite mit Gruppierung der Artikel&#xa;shorturl: Diese Seite soll den Besucher auf die in Page config angegebene Seite umleiten.',
    'pageadmin_edit_pagegroup' => 'Page group:',
    'pageadmin_edit_pagegroup_tooltip' => 'In welcher Kategorie soll diese Seite in der Seitenverwaltung gruppiert werden?',
    'pageadmin_edit_pageconfig' => 'Page config:',
    'pageadmin_edit_pageconfig_tooltip' => 'Beim Seitentyp shorturl, hier das Ziel der Weiterleitung angeben, z.B.: http://www.haase-it.com/&#xa;Beim allen anderen Seitentypen ein valides JSON Objekt mit Seitenspezifischen Daten.&#xa;Beim Seitentyp itemoverview/itemoverviewgrpd hier den Artikelindex angeben:&#xa;{&#34;itemindex&#34;:&#34;A01&#34;} oder {&#34;itemindex&#34;:[&#34;A01&#34;, &#34;A02&#34;]}',
    'pageadmin_edit_subnavarea' => 'Page Subnavarea:',
    'pageadmin_edit_subnavarea_tooltip' => 'Welche Subnavigation soll auf dieser Seite eingeblendet werden?',
    'pageadmin_edit_submit' => 'Submit',
    'pageadmin_edit_deletetitle' => 'Seite löschen',
    'pageadmin_edit_deleteconfirm' => 'Ich möchte diese Seite in allen Sprachen löschen',
    'pageadmin_edit_deletesubmit' => 'Löschen',
    'pageadmin_deleted' => 'Die Seite wurde gelöscht.',

    'pageadmin_exception_couldnotinsertlang' => 'Der Sprachdatensatz konnte nicht angelegt werden.',
    'pageadmin_exception_pagetodeletenotfound' => 'Die zu löschende Seite konnte nicht gefunden werden.',
    'pageadmin_exception_pagenotfound' => 'Die gewünschte Seite konnte nicht gefunden werden.',
    'pageadmin_exception_couldnotinsertpage' => 'Die gewünschte Seite konnte nicht angelegt werden.',

    'pageadmin_archive_lang_title' => 'Archivierte Versionen dieser Seite (Sprachabhängige Daten):',

    'adminhome_enabled' => 'aktiviert',
    'adminhome_disabled' => 'deaktiviert',
    'adminhome_not' => 'NICHT',
    'adminhome_yes' => 'JA',
    'adminhome_no' => 'NEIN',
    'adminhome_writable' => 'erlaubt',

    'adminhome_title' => 'Willkommen im Administrationsbereich',
    'adminhome_teaser' => 'Auf dieser Seite erhalten Sie eine kurze Übersicht über die Erfüllung der Systemvorraussetzungen zum Betrieb dieses Systems.',
    'adminhome_phpextensions' => 'PHP Extensions:',
    'adminhome_phpextensions_filter' => 'Filter ist:',
    'adminhome_directories' => 'Ordnerrechte:',
    'adminhome_templatecache' => 'Der Templatecache-Ordner',
    'adminhome_htmlpurifier' => 'Der HTMLPurifiercache-Ordner',
    'adminhome_log' => 'Der Log-Ordner',
    'adminhome_directory_2' => 'existiert:',
    'adminhome_directory_3' => ', und das Schreiben in den Ordner ist',
    'adminhome_apache' => 'Apache Webserver',
    'adminhome_apache_modrewrite' => 'Das Apache-Modul mod_rewrite ist',
    'adminhome_encryptiontool' => 'Verschlüsselungstool für Admin Passwörter (diese müssen in der Datei config/secrets.local.yml gesetzt werden)',
    'adminhome_encryptiontool_label' => 'Das zu verschlüsselnde Passwort:',
    'adminhome_encryptiontool_submit' => 'Absenden',
    'adminhome_encryptiontool_result_1' => 'Das verschlüsselte Wert für',
    'adminhome_encryptiontool_result_2' => 'ist:',

    'tool_makelissttable_no_config' => 'makeListtable() -> Das Konfigurationsarray ist leer (das erste Argument des Funktionsaufrufs).',

    'textcatadmin_edit_legend' => 'Text bearbeiten',
    'textcatadmin_edit_textkey' => 'Textschlüssel',
    'textcatadmin_edit_language' => 'Sprache',
    'textcatadmin_edit_text' => 'Text',
    'textcatadmin_edit_submit' => 'Speichern',

    'textcatadmin_delete_legend' => 'Text löschen',
    'textcatadmin_delete_confirm' => 'Ich möchte diesen Text in allen Sprachen löschen.',
    'textcatadmin_delete_submit' => 'Löschen',
    'textcatadmin_delete_done' => 'Der Text wurde gelöscht.',

    'textcatadmin_error_keytooshort' => 'Der Textschlüssel muß aus mindestens 3 Zeichen bestehen.',
    'textcatadmin_error_keytoolong' => 'Der Textschlüssel darf aus höchstens 64 Zeichen bestehen.',
    'textcatadmin_error_keyalreadyexists' => 'Dieser Textschlüssel ist bereits angelegt.',
    'textcatadmin_error_invalidcharacter' => 'Dieser Schlüssel enthält ungültige Zeichen, bitte benutzen Sie nur Buchstaben, Zahlen und den Unterstrich ( _ ).',

    'textcatadmin_add_done_1' => 'Der Schlüssel',
    'textcatadmin_add_done_2' => 'wurde hinzugefügt.',
    'textcatadmin_add_done_clickheretoedit' => 'Klicken Sie hier um ihn zu bearbeiten',

    'textcatadmin_add_label' => 'Neuen Textschlüssel hinzufügen:',
    'textcatadmin_add_placeholder' => 'Textschlüssel',
    'textcatadmin_add_submit' => 'Anlegen',

    'textcatadmin_list_add' => 'Neuen Textschlüssel anlegen',
    'textcatadmin_list_title_key' => 'Textschlüssel',
    'textcatadmin_list_title_text' => 'Text',
    'textcatadmin_list_title_edit' => 'bearb.',

    'textcatadmin_archive_lang_title' => 'Archivierte Versionen dieses Textes (Sprachabhängige Daten):',

    'customeradmin_search_label' => 'Anzeigen:',
    'customeradmin_search_all' => 'Alle',
    'customeradmin_search_active' => 'Aktive',
    'customeradmin_search_inactive' => 'Inaktive',
    'customeradmin_search_submit' => 'Los',

    'customeradmin_info_nodatafound' => 'Es wurden keine zu Ihren Suchkriterien passenden Benutzer-Datensätze gefunden.',
    'customeradmin_info_nosuchuserfound' => 'Keine entsprechender Benutzer gefunden.',
    'customeradmin_info_passwordchanged' => 'Das Passwort wurde geändert.',

    'customeradmin_list_no' => 'Nr.',
    'customeradmin_list_company' => 'Firma',
    'customeradmin_list_name' => 'Name',
    'customeradmin_list_town' => 'Ort',
    'customeradmin_list_active' => 'Aktiv',
    'customeradmin_list_edit' => 'bearb.',

    'itemgroupadmin_info_duplicateno' => 'Diese Gruppennummer wird bereits für eine andere Gruppe verwendet.',
    'itemgroupadmin_info_groupjustadded' => 'Die Gruppe wurde hinzugefügt.',
    'itemgroupadmin_info_nolanguagespecificdataexists' => 'In dieser Sprache sind noch keine Texte angelegt!',
    'itemgroupadmin_info_addlanguagespecificdata' => '[Anlegen]',
    'itemgroupadmin_error_nametooshort' => 'Der Name ist zu kurz.',
    'itemgroupadmin_error_grouptooshort' => 'Die Gruppennummer ist zu kurz.',
    'itemgroupadmin_error_duplicateno' => 'Diese Gruppennummer ist bereits vergeben.',
    'itemgroupadmin_error_nogroupsavailable' => 'Zur Zeit sind keine Gruppen angelegt.',

    'itemgroupadmin_name' => 'Gruppenname:',
    'itemgroupadmin_no' => 'Gruppennummer:',
    'itemgroupadmin_image' => 'Bild:',
    'itemgroupadmin_text' => 'Kurztext:',
    'itemgroupadmin_details' => 'Details:',
    'itemgroupadmin_submit' => 'Speichern',
    'itemgroupadmin_add' => '[Eine neue Gruppe anlegen]',

    'itemgroupadmin_list_no' => 'Gruppennr.',
    'itemgroupadmin_list_name' => 'Gruppenname',
    'itemgroupadmin_list_edit' => 'bearb.',

    'shopadmin_list_orderid' => 'ID',
    'shopadmin_list_customer' => 'Kunde',
    'shopadmin_list_sumnettoall' => 'Netto',
    'shopadmin_list_orderstatus' => 'Status',
    'shopadmin_list_ordertimenumber' => 'Zeit/VorgNr',
    'shopadmin_list_hostpayment' => 'Zahlungsinfo',
    'shopadmin_list_edit' => 'bearb.',

    'shopadmin_list_itemno' => 'Art Nr',
    'shopadmin_list_itemname' => 'Art Name',
    'shopadmin_list_itemamount' => 'Menge',
    'shopadmin_list_itemnetto' => 'Netto',
    'shopadmin_list_itemsumnetto' => 'Ges. Netto',

    'shopadmin_orderstatus_completed' => 'erledigt',
    'shopadmin_orderstatus_open' => 'offen',
    'shopadmin_orderstatus_inwork' => 'in Arbeit',
    'shopadmin_orderstatus_canceled' => 'storniert',
    'shopadmin_orderstatus_deleted' => 'gelöscht',
    'shopadmin_orderstatus_allvalid' => 'alle gültigen',
    'shopadmin_openorinwork' => 'offen oder in Arbeit',

    'shopadmin_searchform_state' => 'Zustand:',
    'shopadmin_searchform_from' => 'Von:',
    'shopadmin_searchform_to' => 'Bis:',
    'shopadmin_searchform_submit' => 'Los...',
    'shopadmin_searchform_remark' => '(Datumseingrenzung nur bei Filtern nach Zustand "Alle", "Abgeschlossen" oder "Gelöschte" berücksichtigt.)',

    'shopadmin_info_customerwasloggedin' => 'Der Kunde war mit seinen Benutzerdaten am Webshop angemeldet.',
    'shopadmin_info_customerwasnotloggedin' => 'Der Kunde war nicht angemeldet.',
    'shopadmin_info_tos_accepted_1' => 'Die AGBs wurden',
    'shopadmin_info_tos_accepted_2' => 'akzeptiert.',
    'shopadmin_info_not' => 'nicht',
    'shopadmin_info_cancellationdisclaimer_accepted_1' => 'Die Widerrufsbelehrung wurde',
    'shopadmin_info_cancellationdisclaimer_accepted_2' => 'akzeptiert.',

    'shopadmin_internalremarks' => 'Bemerkungen intern',
    'shopadmin_internaltransactionno' => 'Interne Vorgnr',
    'shopadmin_paymentstatus' => 'Zahlungsstatus',
    'shopadmin_paymentstatus_open' => 'Offen',
    'shopadmin_paymentstatus_paid' => 'Bezahlt',
    'shopadmin_orderstatus' => 'Status',
    'shopadmin_shippingservice' => 'Paketdienst',
    'shopadmin_trackingno' => 'Trackingnr',
    'shopadmin_submit' => 'Speichern',
    'shopadmin_customerremarks' => 'Bemerkungen des Kunden:',

    'shopadmin_itemsordered' => 'Die folgenden Artikel wurden bestellt:',
    'shopadmin_ordersubmitteddate' => 'Die Bestellung wurde aufgegeben am:',
    'shopadmin_orderlasteditdate' => ', die letzte Bearbeitung erfolgte am:',
    'shopadmin_orderlasteditby' => '<br>Die letzte Bearbeitung erfolgte durch:',
    'shopadmin_orderselectedpaymentmethod' => 'Die vom Kunden gewünschte Zahlungsmethode ist:',
    'shopadmin_orderpaypalpaymentconfirmed' => 'Laut PayPal ist die Zahlung bereits erfolgt, die Transaktions ID des Vorgangs ist:',
    'shopadmin_orderpaypalpaymentnotconfirmed' => 'Laut PayPal ist die Zahlung noch nicht erfolgt.',
    'shopadmin_orderdomain_1' => 'Die Bestellung wurde &uuml;ber die Domain:',
    'shopadmin_orderdomain_2' => 'aufgegeben.',

    'shopadmin_info_ordernotfound' => 'Die gewünschte Bestellung wurde nicht gefunden.',
    'shopadmin_info_nomatchingordersfound' => 'Es wurden keine zu Ihren Suchkriterien passenden Bestell-Datensätze gefunden.',
    'shopadmin_info_ordersshown_1' => 'Bestellung(en) angezeigt',
    'shopadmin_info_ordersshown_2' => ', davon',
    'shopadmin_info_ordersshown_3' => 'stornierte (diese werden bei den Wertberechnungen nicht ber&uuml;cksichtigt).',
    'shopadmin_info_ordersshownsum' => 'Gesamtnetto der angezeigten Bestellungen:',
    'shopadmin_info_ordersshownaveragesum' => 'Durchschnittlicher Nettobestellwert der angezeigten Bestellungen:',

    'newcustomerregistration_mail_subject' => 'Neue Kundenregistrierung',
    'newcustomerregistration_mail_text1' => 'Von:',
    'newcustomerregistration_mail_text2' => '<br>Am:',

    'dbstatus_title' => 'DB Status',
    'dbstatus_orphanedrowsinfo' => 'Anzahl der verwaisten Datensätze in',
    'dbstatus_orphanedrowsremove' => 'Klicken Sie hier um sie zu löschen.',
];
