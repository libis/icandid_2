import Vue from 'vue'
import { MLInstaller, MLCreate, MLanguage } from 'vue-multilanguage'
 
Vue.use(MLInstaller)
 
export default new MLCreate({
  initial: 'nl',
  save: process.env.NODE_ENV === 'production',
  languages: [
    new MLanguage('en').create({
        language:"Language",
        en:"English",
        nl:"Nederlands",
        thislanguage:"English",
        projecttitle: "iCANDID",
        search: 'Search',
        searchresult: 'Searchresult',
        advancedsearch: 'Advanced Search',
        clearsearch:'Clear Search',
        simplesearch: 'Simple Search',
        suredeleteitemfromset : 'Are you sure you wish to delete this record from the set ?',
        suredeletesearchfromsaved : 'Are you sure you wish to delete this search from the saved searches ?',
        suredeleteset: 'Are you sure you wish to delete this complete set ?',
        statistics:'Statistics',
        about:'About',
        abouticandid:"About iCANDID",
        aboutdatasets:"About datasets",
        aboutcitations:"Cite iCANDID",
        howtocite:"How to cite iCANDID",
        help:'Help',
        profile:'Profile',
        logout:'Logout',
        login:'Login',
        savedsearches:'Saved Searches',
        savedrecords:'Saved Records',
        searchhistory:'Search History',
        allthewords:'All the words',
        oneofwords:'One of the words',
        exactsentence:'The exact sentence',
        yesterday:'Yesterday',
        lastweek:'Last week',
        lastmonth:'Last month',
        oneyear:'For one year',
        entirearchive:'Entire archive',
        all:'All',
        savequery:'Save query for later use',
        exportcsv:'Export the full result as CSV',
        exporttxt:'Export the full result as TXT',
        exportxlsx:'Export the full result as Excel',
        exportjson:'Export the full result as JSON-LD',
        exportsetcsv:'Export collection as CSV',
        exportsetxlsx:'Export collection as Excel',
        exportsettxt:'Export collection as TXT',
        exportsetjson:'Export collection as JSON-LD',
        platformby:'Platform by',
        type: 'Type',
        identifier: 'Identifier',
        publication:'Publication',
        edition:"Edition",
        publicationdate: 'Publicationdate',
        dateline: 'Dateline',
        title: 'Title',
        title_warn: "Title is required",
        author: 'Author',
        description: 'Description',
        text: 'Text',
        keywords: 'Keywords',
        mentions: 'Mentions',
        duration: 'Duration',
        content: 'Content',
        link:'Link',
        permalink: 'Permalink',
        provider: 'Provider',
        provider_warn: "Provider is required",
        dataset:"Dataset",
        saveiteminset:'Save record(s) in \'Saved records\'',
        filters:'Filters',
        hits :'Hits',
        publisher:"Publisher",
        enternameofset:'Enter the name of the set you wish the item to be added to.',
        alternativeclick:'Alternatively double-click on the field below to get a list of the names of the sets you already have.',
        save:'Save',
        cancel:'Cancel',
        articlesperweek:'Articles per week',
        totalnumberofarticles:'Total number of articles',
        numberofarticlespernewssource:'Number of articles per newssource',
        days:'days',
        articlespernewssourceperday : "Articles per newssource per day",
        numberofarticlespertype:'Number of articles per type',
        last:'Last',
        accessdenied:'Access denied',
        youhavenoaccess:'You have no access',
        recordnotfound:'Record not found',
        sortby:'Sort by',
        relevance:'Relevance',
        noresultsfound:'No results found',
        notice:'Notice',
        info:'Info',
        confirm:'Confirm',
        personalprofile:"Personal Profile",
        name:'Given name',
        lastname:'Last Name',
        fullname:"Name",
        email:'Email',
        and:"and",
        or:"or",
        not:"not",
        containsthewords:"contains the words",
        containsexactphrase:"contains the exact phrase",
        startswith:"starts with",
        any:"Any",
        exportinprogress:"Your export is being prepared.<br><br>You will receive a message at <b>{email}</b> when it is ready for download.",
        searchsaved:"The search is saved.",
        yes:"Yes",
        no:"No",
        ok:"Ok",
        period:"Period",
        from:"From",
        toandincluding:"to and including",
        dbinfotooltip:"Most databases require you to register and login to view the source record in their environment.<br/>You can find more information on how to get access in the <a href=\"/#/help\">Help</a> section.",
        NERModelsinuse:"NER models in use",
        sender:"Sender",
        recipient:"Reply to",
        ner_label_GPE:"Geopolitical entity",
        ner_label_PERSON:"People",
        ner_label_ORG:"Organization",
        ner_label_NORP:"Nationalities or religious or political groups",
        ner_label_FAC:"Building",
        ner_label_WORK_OF_ART:"Work of art",
        ner_label_LOC:"Geographical locations",
        ner_label_EVENT:"Event",
        ner_label_DATE:"Date",
        ner_label_CARDINAL:"Cardinal number",
        ner_label_ORDINAL:"Ordinal number",
        ner_label_PRODUCT:"Object",
        ner_label_LANGUAGE:"Language",
        ner_label_TIME:"Time",
        ner_label_LAW:"Laws",
        ner_label_QUANTITY:"Quantity",
        ner_label_MONEY:"Monetary values",
        ner_label_PERCENT:"Percentage",
        retweeted_tweet:"Retweeded tweet",
        onlyretweet:"Only retweets",
        noretweet:"No retweets",
        retweet:"Retweets",
        retweets:"Retweets",
        replied_to_tweet:"Replied to tweet",
        quoted_tweet:"Quoted tweet",
        eula:"Terms of use",
        visualizations:"Visualizations",
        heatmap:"Word Heatmap",
        heatmap_info:"A heatmap is a visualization technique that represents the size of data using color. With this graph, you can visualize in how many records from your search results a word occurs over time and per publication. At the bottom, you can enter a search term and press 'enter'. Using color shades, you can see in how many records from your search results the term occurs. That frequency is calculated based on the number of records in which the term occurs. When a term occurs multiple times in the same record, it is counted as one occurrence.",
        treemap:'Publications Treemap',
        treemap_info:"A treemap is a method of visualizing data using figures. In this case, rectangles are used to visualize in which publications the search terms you entered appear most often. That frequency is calculated based on the number of records in which the term occurs. When a term occurs multiple times in the same record, it is counted as one occurrence.",
        bargraph:"Words Bargraph",
        bargraph_info:"The bargraph shows the frequency distribution of words in the records from your search results. With this graph, you can visualize in how many records from your search results a word occurs over time. That frequency is calculated based on the number of records in which the term is used. When a term occurs several times in the same record, this is counted as one occurrence.</p><p>For this you need to enter additional search terms in the appropriate field on the right. If you have 100 search results and enter the term \"face mask\", the bargraph shows in how many of the 100 records the word occurs from day to day. After entering a term, press 'enter' and you can enter more terms if desired.</p><p>If the bargraph is difficult to read due to the amount of search results, you can always search within a shorter period of time.", 
        bubblechart:"Authors Bubblechart",
        bubblechart_info:"The bubble chart is used to visualize the relative amount of contributions made by an author in your search results. The size of the figures clearly show which author contributed to the most records within your results. Depending on the amount of records in your search results, it is possible that not all authors are shown, but only the most frequently occuring ones",
        wordcloud:"Wordcloud",
        wordcloudner:"Wordcloud Named Entities",
        wordcloudner_info:"The Wordcloud shows the named entities that appear most often in your search results. The size of a named entity in the word cloud reflects the frequency of use. A named entity is a proper name that refers to a person, location, organization, product, etc. The named entities are retrieved from the records via automated named-entity recognition.",
        top10:"Top 10",
        top10_info:"Two things are shown in this visualization. On the left, the top ten news sources are listed in order of which ones your searched keywords appears most often. On the right, the top ten named entities that appear in your search results most often are listed. A named entity is a proper name that refers to a person, location, organization, product, etc. The named entities are retrieved from the records via automated named-entity recognition.",
        network:"Network",
        requestnewsletter:"Would you like to receive updates about new developments in iCANDID?",
        network_info:"This visualization only applies to search results from Twitter. It shows you the interactions between twitter profiles within the records resulting from your search. Retweets are shown in the visualization with the originally referenced profile in the center. The larger this central figure is compared to other figures, the more often it is referred to via retweet, relatively speaking.",
        home_title:"About iCANDID",
        home_intro:"iCANDID offers innovative and integrated access to metadata about various types of textual and audio-visual material from the main news databases in Flanders (GoPress Academic and ENA), as well as freely accessible online sources (e.g. Twitter data). iCANDID allows users to efficiently search for relevant text and fragments in large amounts of unstructured data. iCANDID includes export functionalities to ensure that researchers working with large datasets of socially significant data can analyze this data using existing analytical tools for quantitative and qualitative textual analysis as well as software tools for automated visual analysis.",
        home_limited:"iCANDID is currently only available to KU Leuven staff and students and access to the platform needs to be requested. To request access to iCANDID as a KU Leuven user in the context of your research, please contact <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a>.",
        home_notloggedin:"If you have requested and obtained access, you can <a href=\"/login\">login here</a>",
        home_moreinfo:"More information about the iCANDID platform, the data that can be discovered and the terms of use can be found in the 'About' section.",
        onesearchterm:"Please enter at least 1 searchterm",
        selectall:"Select all results",
        requests:"Requests",
        dataset_on_demand:"Dataset-request",
        access:"Access-request",
        namefirstname:"Name and given name",
        namefirstname_warn:"Name and given name are mandatory",
        email_warn:"Email is invalid",
        institution:"Institution",
        kuleuven:"KU Leuven",
        other:"Other",
        institutionname:"Name of institution",
        institutionname_warn:"Name of institution is mandatory",
        functiontitle:"Position",
        researcher:"Researcher",
        student:"Student",
        functiontitle_warn:"Position is mandatory",
        promotor:"Promotor",
        promotor_warn:"Promotor is mandatory",
        faculty:"Faculty",
        faculty_warn:"Faculty is mandatory",
        researchgroup:"Research group",
        researchgroup_warn:"Researchgroup is mandatory",
        personel_student_no:"Personnel or studentnumber",
        personel_student_no_warn:"Personnel or studentnumber is mandatory",
        requestreason:"Motivation for the request",
        requestreason_warn:"Motivation for the request is mandatory",
        requestduration:"Duration",
        requestduration_warn:"Duration is mandatory",
        accessicandidmedia1:"Access to iCANDID Media (Official pressmedia / social pressmedia)",
        accessicandidmedia2:"Access to iCANDID Media (ENA / social pressmedia)",
        accesscollectionregister:"Access to iCANDID Collectionregister",
        datasets:"Datasets",
        chooseonedataset:"Choose at least one dataset",
        requestfunctionality:"Which functionalities do you wish to access?",
        chooseone:"Choose at least one",
        termsofuse:"I accept the <a href=\"/#/eula\" target=\"_blank\">terms of use</a>",
        requestanswerpos:": <p>Your request is being processed.<p> <br><p>iCANDID is run by a small team. Applications are usually processed within one month.<p><br><p>For additional questions, please contact <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a> </p> ",
        requestanswerneg:"<p class=\"has-text-danger\">Something went wrong with your request.<p> <br><p>Please contact <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a></p>",
        back:"Back",
        next:"Next",
        sendrequest:"Send request",
        refresh:"Refresh",
        searchterms: "Searchterms or searchquery",
        searchterms_warn: "Searchterms or searchquery is required",
        additionalinfo:"Additional information",
        edit:"Edit",
        apikey:"API-key",
        newapikey:"New API-key",
        delete:"Delete",
        eppn:"Loginname",
        newuser:"New user",
        newapikeyph:"API-key will be automatically generated when saving",
        users:"Users",
        newdataset:"New dataset",
        internalident:"Internal Identifier",
        talen:"Languages",
        labels:"Labels",
        label:"Label",        
        requestor:"Requestor/creator",
        requestoremail:"Email of requestor/creator",
        query:"Searchquery"  , 
        available:"Available",
        groups:"Groups",
        newgroup:"New group",
        newlabel:"New label",
        accessrights:"Accessrights",
        function:"Functionality",
        admin:"Admin",   
        media:"Media",
        collections:"Collection Registry",
        notavailable:"Not available",
        showonlyavailablecollections:"Only show available collections",
        cleardates:"Clear dates",
        reallydeluser:"Are you sure you wish to delete this user ? This cannot be undone !",
        size:"Size",
        documents:"records",
        youalreadyhaveaccess:"You have access to this collection",
        requestdatasetaccess:"Request access",
        unavailable:"Unavailable",
        allusers:"All users",
        activeusers:"Active users",
        inactiveusers:"Inactive users",
        active:"Active",
        fieldrequired:"This field is required",
        validityperiod:"Validity period",
        message:"Message",
        lastcount:"last count on",
        license:"License",
        texts:"Texts",
        preview:"Preview",
        collections_:"Collections",
        functions:"Functions",
        selectevery:"select all",
        selectnone:"select none",
        hidden:"Hidden",
        sensitivecollection:"(for collections with sensitive content)",
        periodinfotooltip:"Use advanced search \"period\" to search on a specific date range",
        newsletter:"Newsletter",
        exportusers:"Export all users as Excel",
        pagination:"Pages",
        articlesection:"Section",
        location:"Location",
        sdDatePublished:"Stored in iCandid on",
        updatetime:"Last modified in iCandid on",
        twitter:"Twitter",
        apikeysecret:"API-key Secret",
        bearertoken:"Bearer Token",
        name_warn:"Name is mandatory",
        lastname_warn:"Lastname is mandatory",
        userexists:"A user with this loginname already exists",
        legislationPassedBy:"Petitioner",
        legislationType:"Documentype",
        legislationResponsible:"Responsible minister"
    }),
 
    new MLanguage('nl').create({
        language:"Taal",
        en:"English",
        nl:"Nederlands",
        thislanguage:"Nederlands",
        projecttitle: "iCANDID",
        search: 'Zoeken',
        searchresult: 'Zoekresultaat',
        advancedsearch: 'Geavanceerd zoeken',
        clearsearch:'Wissen',
        simplesearch: 'Eenvoudig zoeken',
        suredeleteitemfromset : 'Ben je zeker dat je dit record wil verwijderen uit de collectie ?',
        suredeletesearchfromsaved : 'Ben je zeker dat je deze zoekopdracht wil verwijderen uit de opgeslagen zoekopdrachten ?',
        suredeleteset: 'Ben je zeker dat je deze volledige collectie wil verwijderen ?',
        statistics:"Statistieken",
        about:"Over",
        abouticandid:"Over iCANDID",
        aboutdatasets:"Over datasets",
        aboutcitations:"Citeer iCANDID",
        howtocite:"Hoe iCANDID citeren",
        help:"Help",
        profile:"Profiel",
        logout:"Afmelden",
        login:'Aanmelden',
        savedsearches:"Opgeslagen zoekopdrachten",
        savedrecords:"Opgeslagen records",
        searchhistory:"Zoekgeschiedenis",
        allthewords:'Alle woorden',
        oneofwords:'Eén van de woorden',
        exactsentence:'De exacte zin',
        yesterday:'Gisteren',
        lastweek:'Voorbije week',
        lastmonth:'Voorbije maand',
        oneyear:'Voorbije jaar',
        entirearchive:'Volledig archief',
        all:'Alle',
        savequery:'Zoekopdracht opslaan',
        exportcsv:'Exporteer het volledige resultaat als CSV',
        exporttxt:'Exporteer het volledige resultaat als TXT',
        exportxlsx:'Exporteer het volledige resultaat als Excel',
        exportjson:'Exporteer het volledige resultaat als JSON-LD',
        exportsetcsv:'Exporteer collectie als CSV',
        exportsetxlsx:'Exporteer collectie als Excel',
        exportsettxt:'Exporteer collectie als TXT',
        exportsetjson:'Exporteer collectie als JSON-LD',
        platformby:'Platform door',
        type: 'Type',
        identifier: 'Identificator',
        publication:'Publicatie',
        edition:"Editie",
        publicationdate: 'Publicatiedatum',
        dateline: 'Datumlijn',
        title: 'Titel',
        title_warn: "Titel is verplicht",
        author: 'Auteur',
        description: 'Beschrijving',
        text: 'Tekst',
        keywords: 'Kernwoorden',
        mentions: 'Vermeldingen',
        duration: 'Duurtijd',
        content: 'Inhoud',
        link:'Link',
        permalink: 'Permanente link',
        provider: 'Provider',
        provider_warn: "Provider is verplicht",
        dataset:"Dataset",
        saveiteminset:'Bewaar record(s) in \'Opgeslagen records\'',
        filters:"Filters",
        hits:"Aantal",
        publisher:"Publicatie",
        enternameofset:'Voer de naam van de collectie, waaraan je deze records wil toevoegen, in.',
        alternativeclick:'Of dubbelklik hieronder om een lijst van reeds bestaande collecties te krijgen.',
        save:'Opslaan',
        cancel:'Annuleren',
        articlesperweek:'Artikels per week',
        totalnumberofarticles:'Totaal aantal artikels',
        numberofarticlespernewssource:'Aantal artikels per nieuwsbron',
        days:'dagen',
        articlespernewssourceperday : "Artikels per nieuwsbron per dag",
        numberofarticlespertype:'Aantal artikels per type',
        last:'Laatste',
        accessdenied:'Geen toegang',
        youhavenoaccess:'U heeft geen toegang',
        recordnotfound:'Record niet gevonden',
        sortby:'Sorteer op',
        relevance:'Relevantie',
        noresultsfound:'Geen resultaten gevonden',
        notice:'Melding',
        info:'Info',
        confirm:'Bevestig',
        personalprofile:"Persoonlijke informatie",
        name:'Voornaam',
        lastname:'Familienaam',
        fullname:"Naam",
        email:'Email',
        and:"en",
        or:"of",
        not:"niet",
        containsthewords:"bevat de woorden",
        containsexactphrase:"bevat de exacte zin",
        startswith:"begint met",
        any:"Alle",
        exportinprogress:"Uw export wordt klaargemaakt.<br><br>U zal een email ontvangen via <b>{email}</b> als deze klaar is.",
        searchsaved:"De zoekopdracht is opgeslagen",
        yes:"Ja",
        no:"Nee",
        ok:"Ok",
        period:"Periode",
        from:"Vanaf",
        toandincluding:"tot en met",
        dbinfotooltip:"Bij de meeste databases moet je registreren en inloggen om het bronrecord te kunnen raadplegen.<br/>Meer informatie kan je vinden in de <a href=\"/#/help\">Help</a> sectie.",
        NERModelsinuse:"NER modellen in gebruik",
        sender:"Afzender",
        recipient:"Antwoord op"        ,
        ner_label_GPE:"Geopolitieke entiteit",
        ner_label_PERSON:"Personen",
        ner_label_ORG:"Organizatie",
        ner_label_NORP:"Nationaliteit of religieuze of politieke groep",
        ner_label_FAC:"Gebouw",
        ner_label_WORK_OF_ART:"Kunstwerk",
        ner_label_LOC:"Geografische locatie",
        ner_label_EVENT:"Gebeurtenis",
        ner_label_DATE:"Datum",
        ner_label_CARDINAL:"Hoofdtelwoord",
        ner_label_ORDINAL:"Rangtelwoord",
        ner_label_PRODUCT:"Voorwerp",
        ner_label_LANGUAGE:"Taal",
        ner_label_TIME:"Tijdstip",
        ner_label_LAW:"Wet",
        ner_label_QUANTITY:"Hoeveelheid",
        ner_label_MONEY:"Bedrag",
        ner_label_PERCENT:"Percentage",
        retweeted_tweet:"Geretweete tweet",
        onlyretweet:"Enkel retweets",
        noretweet:"Geen retweets",
        retweet:"Retweets",
        retweets:"Retweets",
        replied_to_tweet:"Beantwoordde tweet",
        quoted_tweet:"Geciteerde tweet",
        eula:"Gebruikersvoorwaarden",
        visualizations:"Visualisatie",
        heatmap:"Woord Heatmap",
        heatmap_info:'Een heatmap is een visualisatietechniek die de omvang van data weergeeft met behulp van kleur. Met deze optie kan je visualiseren in hoeveel records uit je zoekresultaten dat woord voorkomt doorheen de tijd en per publicatie. Onderaan kan je een zoekterm ingeven en ‘enter’ indrukken. Aan de hand van kleurschakeringen wordt vervolgens getoond in hoeveel records die resulteerden uit jouw zoekopdracht de term voorkomt. Die frequentie wordt berekend op basis van de hoeveelheid records waarin de term voorkomt. Wanneer een term meermaals in hetzelfde record voorkomt, wordt dit als één voorval geteld.',
        treemap:'Publicaties Treemap',
        treemap_info:'Een treemap is een methode om data te visualiseren met behulp van figuren. In dit geval worden rechthoeken gebruikt om te visualiseren in welke publicaties de door jou ingegeven zoektermen het vaakste voorkomen. Die frequentie wordt berekend op basis van de hoeveelheid records waarin de term voorkomt. Wanneer een term meermaals in hetzelfde record voorkomt, wordt dit als één voorval geteld.',
        bargraph:"Woorden Bargraph",
        bargraph_info:'Met de bargraph wordt de frequentieverdeling van woorden in de records uit jouw zoekresultaten getoond. Met deze optie kan je visualiseren in hoeveel records uit je zoekresultaten dat woord voorkomt doorheen de tijd. Die frequentie wordt berekend op basis van de hoeveelheid records waarin de term voorkomt. Wanneer een term meermaals in hetzelfde record voorkomt, wordt dit als één voorval geteld.</p><p>Hiervoor dien je nog bijkomende zoektermen in te geven in het daarvoor voorziene veld rechts. Mocht je 100 zoekresultaten hebben en de term “mondmasker” ingeven, dan toont de bargraph in hoeveel van de 100 records het woord voorkomt van dag op dag. Na het ingeven van een term, druk je op ‘enter’ en kan je desgewenst nog meerdere termen ingeven.</p><p>Mocht de bargraph onoverzichtelijk zijn omwille van de hoeveelheid zoekresultaten, kan je altijd zoeken binnen een kortere tijdspanne.', 
        bubblechart:"Auteurs Bubblechart",
        bubblechart_info:'De bubblechart wordt gebruikt om de relatieve hoeveelheid bijdrages door de auteurs die voorkomen in je zoekresultaten te visualiseren. Door de grootte van de figuren wordt overzichtelijk getoond wie er binnen je resultaten de auteur is van de meeste records. Afhankelijk van de hoeveelheid records er in je zoekresultaten zitten, is het mogelijk dat niet alle auteurs weergegeven worden, maar enkel de vaakst voorkomende.',
        wordcloud:"Wordcloud",
        wordcloudner:"Wordcloud Named Entities",
        wordcloudner_info:'Op de Wordcloud worden de named entities getoond die het vaakst voorkomen in jouw zoekresultaten. De grootte van de named entities geeft de frequentie van het gebruik weer. Een named entity is een eigennaam die verwijst naar een persoon, locatie, organisatie, product, enz. De named entities worden via geautomatiseerde named-entity recognition opgehaald uit de records.',
        top10:"Top 10",
        top10_info:'In deze visualisatie worden twee zaken getoond. Links worden de tien nieuwsbronnen weergegeven in de volgorde waarin jouw zoekopdracht het vaakste in voorkomt. Rechts worden de top tien named entities die voorkomen in de zoekresultaten opgesomt.  Een named entity is een eigennaam die verwijst naar een persoon, locatie, organisatie, product, enz. De named entities worden via geautomatiseerde named-entity recognition opgehaald uit de records.',
        network:"Netwerk",
        network_info:'Deze visualisatie heeft enkel betrekking op de zoekresultaten uit Twitter. Hier wordt getoond hoe de interacties verlopen tussen twitterprofielen binnen de records die voortkomen uit je zoekopdracht. In de visualisatie worden retweets getoond met centraal het oorspronkelijke profiel waarnaar verwezen wordt. Hoe groter dit centrale figuur ten opzichte van de andere figuren, hoe vaker er relatief gezien naar verwezen wordt via retweet.',
        home_title:"Over iCANDID",
        home_intro:"iCANDID biedt innovatieve en geïntegreerde toegang tot de metadata van diverse soorten tekst- en audiovisueel materiaal uit de belangrijkste nieuwsdatabanken in Vlaanderen (GoPress en ENA), alsook vrij toegankelijke online bronnen (vb. Twitter-data). iCANDID maakt het mogelijk om relevante teksten en fragmenten efficiënt te zoeken in grote hoeveelheden ongestructureerde data. iCANDID beschikt over exportfuncties, zodat bestaande analytische tools voor kwantitatieve en kwalitatieve tekstuele analyse toegankelijk worden voor elke onderzoeker die werkt met grote datasets van maatschappelijk significante waarde.",
        home_limited:"iCANDID is momenteel enkel beschikbaar voor KU Leuvenpersoneel en -studenten en hen moet ook bijkomend toegang verleend worden. Om als KU Leuven-gebruiker toegang aan te vragen tot iCANDID in het kader van uw onderzoek, gelieve contact op te nemen via <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a>.",
        home_notloggedin:"Als je toegang verkregen hebt kan je <a href=\"/login\"> hier aanmelden</a>",
        home_moreinfo:"Meer informatie over het iCANDID-platform, de data die er te ontdekken valt en de gebruikersvoorwaarden is te vinden onder ‘Over’.",
        onesearchterm:"Gelieve minstens 1 zoekterm in te voeren.",
        selectall:"Selecteer alle resultaten",
        requests:"Aanvragen",
        dataset_on_demand:"Dataset-aanvraag",
        access:"Toegang-aanvraag",
        namefirstname:"Naam en voornaam",
        namefirstname_warn:"Naam en voornaam zijn verplicht",
        email_warn:"Email is ongeldig",
        institution:"Instelling",
        kuleuven:"KU Leuven",
        other:"Other",
        institutionname:"Naam instelling",
        institutionname_warn:"Naam instelling is verplicht",
        functiontitle:"Positie",
        researcher:"Onderzoeker",
        student:"Student",
        functiontitle_warn:"Positie is verplicht",
        promotor:"Promotor",
        promotor_warn:"Promotor is verplicht",
        faculty:"Faculteit",
        faculty_warn:"Faculteit is verplicht",
        researchgroup:"Onderzoeksgroep",
        researchgroup_warn:"Onderzoeksgroep is verplicht",
        personel_student_no:"Personeel- of studenten-nummer",
        personel_student_no_warn:"Personeel- of studenten-nummer is verplicht",
        requestreason:"Reden aanvraag",
        requestreason_warn:"Reden aanvraag is verplicht",
        requestduration:"Duur aanvraag",
        requestduration_warn:"Duur aanvraag is verplicht",
        accessicandidmedia1:"Toegang tot iCANDID Media (Officiële persmedia / sociale persmedia)",
        accessicandidmedia2:"Toegang tot iCANDID Media (ENA / sociale persmedia)",
        accesscollectionregister:"Toegang tot iCANDID Collectieregister",
        datasets:"Datasets",
        chooseonedataset:"Kies minstens 1 dataset",
        requestfunctionality:"Tot welke functies wilt u toegang?",
        chooseone:"Keuze is verplicht",
        termsofuse:"Ik aanvaard de <a href=\"/#/eula\" target=\"_blank\">gebruikersvoorwaarden</a>",
        requestnewsletter:"Wilt u op de hoogte blijven van nieuwe ontwikkelingen binnen iCANDID?",
        requestanswerpos:"<p>Uw aanvraag wordt behandeld.<p> <br><p>iCANDID heeft een beperkt team. Aanvragen worden doorgaans binnen de maand verwerkt.<p><br><p>Bij bijkomende vragen kunt u contact opnemen met <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a></p>",
        requestanswerneg:"<p class=\"has-text-danger\">Er ging iets fout met uw aanvraag<p> <br><p>Gelieve contact op te nemen met <a href=\"mailto:icandid@kuleuven.be\">icandid@kuleuven.be</a></p>",
        back:"Terug",
        next:"Verder",
        sendrequest:"Aanvraag indienen",
        refresh:"Vernieuw",
        searchterms: "Zoektermen of zoekopdracht",
        searchterms_warn: "Zoektermen of zoekopdracht is verplicht",
        additionalinfo:"Bijkomende informatie",
        edit:"Bewerken",
        apikey:"API-key",
        newapikey:"Nieuwe API-key",
        delete:"Wissen",
        eppn:"Loginnaam",
        newuser:"Nieuwe gebruiker",
        newapikeyph:"API-key wordt automatisch gegenereerd bij opslaan",
        users:"Gebruikers",
        newdataset:"Nieuwe dataset",
        internalident:"Interne Identificatie Code",
        talen:"Talen",
        labels:"Labels",
        label:"Label",
        requestor:"Aanvrager/Auteur",
        requestoremail:"Email van aanvrager/auteur",
        query:"Zoekquery",
        available:"Beschikbaar",
        groups:"Groepen",
        newgroup:"Nieuwe groep",
        newlabel:"Nieuw label",
        accessrights:"Toegangsrechten",
        function:"Functionaliteit",
        admin:'Beheer',   
        media:"Media",
        collections:"Collectieregister",
        notavailable:"Niet beschikbaar",
        showonlyavailablecollections:"Toon enkel beschikbare collecties",
        cleardates:"Wis datums",
        reallydeluser:"Ben je zeker dat je deze gebruiker wenst te wissen ? Dit kan niet ongedaan gemaakt worden.",
        size:"Grootte",
        documents:"records",
        youalreadyhaveaccess:"U heeft toegang tot deze collectie",
        requestdatasetaccess:"Toegang aanvragen",
        unavailable:"Onbeschikbaar",
        allusers:"Alle gebruikers",
        activeusers:"Actieve gebruikers",
        inactiveusers:"Inactive gebruikers",
        active:"Actief",
        fieldrequired:"Dit veld is verplicht",
        validityperiod:"Geldigheidsduur",
        message:"Bericht",
        lastcount:"geteld op ",
        license:"Licentie",
        texts:"Teksten",
        preview:"Voorbeeld",
        collections_:"Collecties",
        functions:"Functionaliteiten",
        selectevery:"selecteer alles",
        selectnone:"selecteer niets",
        hidden:"Verborgen",
        sensitivecollection:"(voor collecties met gevoelige inhoud)",
        periodinfotooltip:"Gebruik \"Geavanceerd zoeken\" op \"Periode\" om te zoeken op een specifieke periode",
        newsletter:"Nieuwsbrief",
        exportusers:"Exporteer alle gebruikers als Excel",
        pagination:"Pagina's",
        articlesection:"Katern",
        location:"Plaats",
        sdDatePublished:"In iCandid opgenomen op",
        updatetime:"Laatste wijzinging in iCandid op",
        twitter:"Twitter",
        apikeysecret:"API-key Secret",
        bearertoken:"Bearer Token",
        name_warn:"Voornaam is verplicht",
        lastname_warn:"Familienaam is verplicht",
        userexists:"Een gebruiker met deze loginnaam bestaat reeds",
        legislationPassedBy:"Indiener",
        legislationType:"Documentsoort",
        legislationResponsible:"Verantwoordelijke minister"
    })
  ]
})