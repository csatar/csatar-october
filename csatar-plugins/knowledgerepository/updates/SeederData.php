<?php namespace Csatar\KnowledgeRepository\Updates;

use Csatar\KnowledgeRepository\Models\AccidentRiskLevel;
use Csatar\KnowledgeRepository\Models\FolkSongRhythm;
use Csatar\KnowledgeRepository\Models\FolkSongType;
use Csatar\KnowledgeRepository\Models\GameDevelopmentGoal;
use Csatar\KnowledgeRepository\Models\MethodologyType;
use Csatar\KnowledgeRepository\Models\Region;
use Csatar\KnowledgeRepository\Models\SongType;
use Csatar\KnowledgeRepository\Models\Tool;
use Csatar\KnowledgeRepository\Models\Headcount;
use Csatar\KnowledgeRepository\Models\Duration;
use Csatar\KnowledgeRepository\Models\Location;
use Csatar\KnowledgeRepository\Models\GameType;
use Csatar\KnowledgeRepository\Models\TrialSystemTopic;
use Csatar\KnowledgeRepository\Models\TrialSystemSubTopic;
use Csatar\KnowledgeRepository\Models\TrialSystemTrialType;
use Csatar\KnowledgeRepository\Models\TrialSystemType;
use Csatar\KnowledgeRepository\Models\TrialSystemCategory;
use Csatar\Forms\Models\Form;
use Db;
use Seeder;

class SeederData extends Seeder
{
    public const DATA = [
        'gameDevelopmentGoals' => [
            [
                'name' => 'Ismerkedős',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bemelegítés, ráhangolódás',
                'sort_order' => 2,
            ],
            [
                'name' => 'Energiaszínt felemeléséhez',
                'sort_order' => 3,
            ],
            [
                'name' => 'Csoport és párképző játék',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tábortűzi játékok',
                'sort_order' => 5,
            ],
            [
                'name' => 'Élmény és történt felidézéséhez',
                'sort_order' => 6,
            ],
            [
                'name' => 'Bizalmi játékok',
                'sort_order' => 7,
            ],
            [
                'name' => 'Együttmüködést fejlesztő',
                'sort_order' => 8,
            ],
            [
                'name' => 'Érzések, empátiát fejlesztő',
                'sort_order' => 9,
            ],
            [
                'name' => 'Kommunikációs készséget fejlesztő',
                'sort_order' => 10,
            ],
            [
                'name' => 'Konfliktus kezelő',
                'sort_order' => 11,
            ],
            [
                'name' => 'Tudásfejlesző vagy gyakorló játék',
                'sort_order' => 12,
            ]
        ],
        'accidentRiskLevels' => [
            [
                'name' => 'Alacsony',
                'sort_order' => 1,
            ],
            [
                'name' => 'Közepes',
                'sort_order' => 2,
            ],
            [
                'name' => 'Magas',
                'sort_order' => 3,
            ],
        ],
        'tools' => [
            [
                'name' => 'Nincs kellék',
                'approved' => true,
            ],
            [
                'name' => 'Papír',
                'approved' => true,
            ],
            [
                'name' => 'Golyóstoll',
                'approved' => true,
            ],
            [
                'name' => 'Olló',
                'approved' => true,
            ],
            [
                'name' => 'Ragasztó',
                'approved' => true,
            ],
            [
                'name' => 'Színes papír',
                'approved' => true,
            ],
            [
                'name' => 'Zsineg/szallag/spárga',
                'approved' => true,
            ],
            [
                'name' => '(Nyak)kendő',
                'approved' => true,
            ],
            [
                'name' => 'Dobókocka',
                'approved' => true,
            ],
            [
                'name' => 'Labda',
                'approved' => true,
            ],
            [
                'name' => 'Lavór',
                'approved' => true,
            ],
            [
                'name' => 'Egyéb',
                'approved' => true,
            ],
            [
                'name' => 'Cserkésznyakkendő',
                'approved' => true,
        ],
        ],
        'headCounts' => [
            [
                'description' => '2-8 fő',
                'min' => 2,
                'max' => 8,
                'note' => '(őrsi találkozó)',
                'sort_order' => 1,
            ],
            [
                'description' => '9-15 fő',
                'min' => 9,
                'max' => 15,
                'note' => '(őrsi és/vagy raji találkozó)',
                'sort_order' => 2,
            ],
            [
                'description' => '16-29 fő',
                'min' => 16,
                'max' => 29,
                'note' => '(raji létszámhoz)',
                'sort_order' => 3,
            ],
            [
                'description' => '30+ fő',
                'min' => 31,
                'max' => 100,
                'note' => '(csapatlétszámhoz)',
                'sort_order' => 4,
        ],
            [
                'description' => 'Bármekkora létszámmal játszható',
                'min' => 0,
                'max' => 0,
                'note' => '',
                'sort_order' => 5,
            ],
        ],
        'durations' => [
            [
                'name' => '1-5 perc',
                'min' => 1,
                'max' => 5,
            ],
            [
                'name' => '6-10 perc',
                'min' => 6,
                'max' => 10,
            ],
            [
                'name' => '7-15 perc',
                'min' => 7,
                'max' => 15,
            ],
            [
                'name' => '16-20 perc',
                'min' => 16,
                'max' => 20,
            ],
            [
                'name' => '21-25 perc',
                'min' => 21,
                'max' => 25,
            ],
            [
                'name' => '26-35 perc',
                'min' => 26,
                'max' => 35,
            ],
            [
                'name' => '36-50 perc',
                'min' => 36,
                'max' => 50,
            ],
            [
                'name' => '51 - 75 perc',
                'min' => 51,
                'max' => 75,
            ],
            [
                'name' => '76 - 90 perc',
                'min' => 76,
                'max' => 90,
            ],
        ],
        'locations' => [
            [
                'name' => 'Kültéri - mező',
                'sort_order' => 1,
            ],
            [
                'name' => 'Kültéri - erdő',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kültéri - udvar',
                'sort_order' => 3,
            ],
            [
                'name' => 'Beltéri - terem',
                'sort_order' => 4,
            ],
            [
                'name' => 'Beltéri - berendezett (székek, asztalok)',
                'sort_order' => 5,
            ],
            [
                'name' => 'Beltéri - okos (projektor, áramellátás)',
                'sort_order' => 6,
            ],
            [
                'name' => 'Virtuális',
                'sort_order' => 7,
            ],
            [
                'name' => 'Bárhol alkalmazható',
                'sort_order' => 8,
            ],
        ],
        'gameTypes' => [
            [
                'name' => 'Elnemmozdulós',
                'sort_order' => 1,
            ],
            [
                'name' => 'Szorakoztató',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kevés mozgást igénylő',
                'sort_order' => 3,
            ],
            [
                'name' => 'Koncentrációs, mozgós',
                'sort_order' => 4,
            ],
            [
                'name' => 'Erőkifejtő, sok mozgás igénylő',
                'sort_order' => 5,
            ],
            [
                'name' => 'Ez alapján nem besorolható',
                'sort_order' => 6,
            ],
        ],
        'forms' => [
            [
                'title' => 'Játék',
                'model' => 'Csatar\KnowledgeRepository\Models\Game',
            ],
            [
                'title' => 'Módszertan',
                'model' => 'Csatar\KnowledgeRepository\Models\Methodology',
            ],
            [
                'title' => 'Próbarendszer',
                'model' => 'Csatar\KnowledgeRepository\Models\TrialSystem',
            ],
            [
                'title' => 'Munkaterv',
                'model' => 'Csatar\KnowledgeRepository\Models\WorkPlan',
			],
            [
                'title' => 'Dal',
                'model' => 'Csatar\KnowledgeRepository\Models\Song',
            ],
        ],
        'trialSystemTopics' => [
            'A cserkészet története',
            'A magyarság kiemelkedő alakjai',
            'Alaki',
            'A cserkészet története',
            'A magyarság kiemelkedő alakjai',
            'Alaki',
            'Állatismeret',
            'Ásvány és kőzetismeret',
            'Az RMCSSZ ismerete',
            'Becslés és mérés',
            'Családi adatok, ünnepek',
            'Cserkész lelkiség',
            'Cserkészinduló, cserkészdalok',
            'Cserkészkapcsolatok',
            'Csillagászat',
            'Egészséges életmód',
            'Egyenruha ismerete',
            'Élet a természetben',
            'Elsősegély',
            'Eszközhasználat',
            'Foglalkozások ismerete',
            'Földrajzi ismeretek',
            'Gombaismeret',
            'Gyógyszerismeret',
            'Határontúli magyarság, nemzetiségeink',
            'Helyi ünnepek',
            'Higiéné',
            'Hírközlési ismeretek',
            'Hitoktatás / Bibliaóra',
            'Hivatalos iratok, ügyintézés',
            'Idegenvezetés',
            'Illemszabályok',
            'Ima',
            'Karitászmunka (egyházközségi szervezésben)',
            'Kerti munka',
            'Környékismeret',
            'Kötélmunkák',
            'Közjogi méltóságok, államigazgatás, önkormányzatok',
            'Közös jelképeink',
            'KRESZ, közlekedési ismeretek',
            'Krónika',
            'Kultúra',
            'Lelki olvasmány',
            'Levélírás',
            'Liturgikus élet',
            'Média',
            'Megismerés',
            'Mentődoboz',
            'Napi jótett, önfegyelem',
            'Nemzeti jelképeink',
            'Népdalok',
            'Népi játékok',
            'Népmesék, mondák',
            'Népművészet, kismesterségek',
            'Népszokások',
            'Növényismeret',
            'Nyelvtudás',
            'Ökumené',
            'Olvasottság',
            'Őrsi élet',
            'Őrsi jótett',
            'Rovásírás',
            'Segítség otthon',
            'Sípjelek, karjelek',
            'Sütés, főzés, tartósítás',
            'Szabás-varrás, kézimunka',
            'Szentírás-ismeret',
            'Színjátszás',
            'Szomszéd országok ismerete',
            'Tábori építmények',
            'Tábori rend',
            'Tábortűz',
            'Tájékozódás',
            'Térképészet',
            'Természet- és környezetvédelem',
            'Természetes anyagok megmunkálása',
            'Tevékenység',
            'Túra',
            'Ünnep',
            'Utazási ismeretek',
            'Vallási Ismeretek',
            'Velemjáró (cserkészfüzet)',
        ],
        'trialSystemSubTopics' => [
            'Testi',
            'Lelki',
            'Szellemi',
        ],
        'trialSystemTypes' => [
            'Alapvető tudás',
            'Egyszer teljesítendő anyag',
            'Irányítottan választandó anyag',
            'Alap kérdések az életemben. Alapjaim/gyökereim.',
            'Alvás, pihenés',
            'Családom és barátaim',
            'Cserkész lelkiség',
            'Cserkészközösségem',
            'Egészségügy',
            'Egyházközségem/gyülekezetem és lakóhelyem',
            'Én-idő a mindennapokban',
            'Épített környezetünk megismerése',
            'Étkezés',
            'Európaiság',
            'Függőségek',
            'Hazám és nemzetem',
            'Időmenedzsment',
            'Ima',
            'Kapcsolataim',
            'Közösségi élet',
            'Lelki olvasmány',
            'Liturgikus élet',
            'Műveltség',
            'Ökumené',
            'Olvasottság',
            'Önismeret',
            'Személyi higiéné',
            'Személyiséglélektan',
            'Szentírás-ismeret',
            'Szexualitás',
            'Társadalmi ismeretek',
            'Természetes környezetünkkel kapcsolatos feladatok',
            'Természetünk és környezetünk megismerése',
            'Testnevelés, Sport',
            'Vallási ismeretek',
        ],
        'trialSystemTrialTypes' => [
            'Piros Pajzs',
            'Fehér Pajzs',
            'Zöld Pajzs',
            'Első',
            'Második',
            'Újonc',
        ],
        'trialSystemCategories' => [
            'Ember és ember',
            'Ember és környezete',
            'Isten és ember',
        ],
        'methodologyTypes' => [
            [
                'name' => 'Új (eddig az őrs még sosem találkozott az átadott anyaggal)',
                'sort_order' => 1
            ],
            [
                'name' => 'Régi/ismétlés (ismétlés, az őrs már találkozott a megnevezett anyaggal)',
                'sort_order' => 2
            ],
            [
                'name' => 'Mindkettő',
                'sort_order' => 3
            ],
        ],
        'songTypes' => [
            'Népdal',
            'Ifidal',
            'Cserkészdal',
        ],
        'folkSongTypes' => [
            [
                'name' => 'Ráolvasások és imák',
                'description' => 'ezek az énekek ősi praktikákból táplálkoznak, a magyar néphit és ősi népi vallásosságára világítanak rá. '
            ],
            [
                'name' => 'Ünnepi dalok, rítusénekek, köszöntők és rigmusok',
                'description' => 'a naptári év jeles napjait, a szertartásokat éneklik meg.. Az ünnepi énekek, jókívánságok jelentős része a termékenységvarázslás, amelyet összekapcsolnak a betegség- és bajelhárítással.'
            ],
            [
                'name' => 'Munkadalok',
                'description' => 'létezéséről a Szent Gellért legenda tudósít.'
            ],
            [
                'name' => 'Siratóénekek',
                'description' => 'a legősibb ősmagyar műfaj képviselői. A halállal, a gyásszal, a magáramaradottsággal szembenéző ember érzelmi megnyilvánulása. '
            ],
            [
                'name' => 'Párosítók',
                'description' => 'a párosítók szövegei sablonosak, ebbe egyszerűen belehelyezik az illető leány és legény nevét.'
            ],
            [
                'name' => 'Szerelmi dalok',
                'description' => 'a magyar népdalok legnépesebb csoportja, amely a legrégiesebb, legszebb költői alkotások és dalok gyűjteménye. Előadásuk nem kötődik meghatározott alkalmakhoz.'
            ],
            [
                'name' => 'Lakodalmi énekek, dalok és vőfélyrigmusok',
                'description' => 'a parasztlakodalom középpontjában a menyasszony és a termékenység áll.'
            ],
            [
                'name' => 'Táncdalok és dudanóták',
                'description' => 'a XX. század elejéig elmaradhatatlan kellékei voltak a táncos mulatságoknak. A dudanóták tréfásak, teli vannak kétértelmű utalásokkal és trágárságokkal.'
            ],
            [
                'name' => 'Bordalok és mulatónóták',
                'description' => 'témája általában a bor, a mámor és az ivás dicsérete. Ezeket a dalokat falun szinte kizárólag a férfiak énekelték.'
            ],
            [
                'name' => 'Tréfás és csúfoló dalok, mondókák',
                'description' => 'társas összejöveteleken elhangzó ironikus versek és rigmusok.'
            ],
            [
                'name' => 'Parasztdalok és keservek',
                'description' => 'szerelmi csalódásokat, foglalkozással járó panaszokat, sérelmeket, általában az emberi élet keserveit fogalmazzák meg.'
            ],
            [
                'name' => 'Bujdosóénekek, vándordalokban',
                'description' => 'a szülőföldhöz való ragaszkodás és a honvágy jelenik meg.'
            ],
            [
                'name' => 'Történeti énekek és katonadalok',
                'description' => 'a magyar népdalok három legnagyobb tematikus csoportjának egyike. Témájuk szerint lehetnek toborzók, sorozási nóták, katonakísérők- és siratók, kaszárnyadalok, menetdalok, csatadalok, leszerelő dalok és hadifogolynóták.'
            ],
            [
                'name' => 'Pásztordalok',
                'description' => 'sokat elárulnak a pusztai emberek élet- és gondolkozásmódjáról, érzelemvilágából.'
            ],
            [
                'name' => 'Szolga- és béres dalok',
                'description' => 'hangjuk panaszos, gúnyos, olykor lázadó.'
            ],
            [
                'name' => 'Arató- és summásdalok',
                'description' => 'a mezei munkásság dalai'
            ],
        ],
        'regions' => [
            [
                'name' => 'Alföld'
            ],
            [
                'name' => 'Bácska',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Kis- és Nagykunság',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Jászság',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Hajdúság',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Nyírség',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Érmellék',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Szamoshát',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Tiszahát',
                'big_parent' => 'Alföld'
            ],
            [
                'name' => 'Dunántúl'
            ],
            [
                'name' => 'Ormánság',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Drávaszög',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Szlavóniai magyarság',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Őrség',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Göcsej',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Bakony erdeje',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Balatonfelvidék',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Kisalföld',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Csallóköz',
                'big_parent' => 'Dunántúl'
            ],
            [
                'name' => 'Felföld/Felvidék',
            ],
            [
                'name' => 'Palócföld',
                'big_parent' => 'Felföld/Felvidék'
            ],
            [
                'name' => 'Zobor vidéke',
                'big_parent' => 'Felföld/Felvidék'
            ],
            [
                'name' => 'Garam mente',
                'big_parent' => 'Felföld/Felvidék'
            ],
            [
                'name' => 'Hegyalja',
                'big_parent' => 'Felföld/Felvidék'
            ],
            [
                'name' => 'Erdély',
            ],
            [
                'name' => 'Partium',
                'big_parent' => 'Erdély'
            ],
            [
                'name' => 'Máramarosi',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Partium',
            ],
            [
                'name' => 'Szilágysági',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Partium',
            ],
            [
                'name' => 'Szatmári',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Partium',
            ],
            [
                'name' => 'Bihari',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Partium',
            ],
            [
                'name' => 'Érmelléki',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Partium',
            ],
            [
                'name' => 'Kalotaszeg',
                'big_parent' => 'Erdély'
            ],
            [
                'name' => 'Torockó',
                'big_parent' => 'Erdély'
            ],
            [
                'name' => 'Mezőség',
                'big_parent' => 'Erdély'
            ],
            [
                'name' => 'Székelyföld',
                'big_parent' => 'Erdély'
            ],
            [
                'name' => 'Aranyosszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Marosszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Udvarhelyszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Háromszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Kézdiszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
                'small_parent' => 'Háromszék',
            ],
            [
                'name' => 'Orbaiszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
                'small_parent' => 'Háromszék',
            ],
            [
                'name' => 'Sepsiszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
                'small_parent' => 'Háromszék',
            ],
            [
                'name' => 'Csíkszék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Alcsík',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
                'small_parent' => 'Csíkszék',
            ],
            [
                'name' => 'Felcsík',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
                'small_parent' => 'Csíkszék',
            ],
            [
                'name' => 'Gyergyószék',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Kászon',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Barcaság',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Gyímes',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Moldva',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'Bukovina',
                'big_parent' => 'Erdély',
                'mid_parent' => 'Székelyföld',
            ],
            [
                'name' => 'nem alkalmazható',
            ],
        ],
        'folkSongRhythms' => [
            [
                'name' => 'gyors',
                'description' => 'gyors tempójú népdalok (csárdás, ugrós) - lehet rá menetelni'
            ],
            [
                'name' => 'lassú',
                'description' => 'lassú tempójú népdalok (keserves, sirató) - nem lehet rá menetelni'
            ]
        ]
    ];

    public function run()
    {
        // Game Development Goals
        foreach ($this::DATA['gameDevelopmentGoals'] as $gameDevelopmentGoalData) {
            $gameDevelopmentGoal = GameDevelopmentGoal::firstOrNew([
                'name' => $gameDevelopmentGoalData['name'],
            ]);
            $gameDevelopmentGoal->sort_order = $gameDevelopmentGoalData['sort_order'];
            $gameDevelopmentGoal->save();
        }

        // Accident Risk Levels
        foreach ($this::DATA['accidentRiskLevels'] as $accidentRiskLevelData) {
            $accidentRiskLevel = AccidentRiskLevel::firstOrNew([
                'name' => $accidentRiskLevelData['name'],
            ]);
            $accidentRiskLevel->sort_order = $accidentRiskLevelData['sort_order'];
            $accidentRiskLevel->save();
        }

        // Tools
        foreach ($this::DATA['tools'] as $toolData) {
            $tool = Tool::firstOrNew([
                'name' => $toolData['name'],
            ]);
            $tool->is_approved = $toolData['approved'];
            $tool->save();
        }

        // Head Counts
        foreach ($this::DATA['headCounts'] as $headCountData) {
            $headCount = Headcount::firstOrNew([
                'description' => $headCountData['description'],
            ]);
            $headCount->min = $headCountData['min'];
            $headCount->max = $headCountData['max'];
            $headCount->note = $headCountData['note'];
            $headCount->sort_order = $headCountData['sort_order'];
            $headCount->save();
        }

        // Durations
        foreach ($this::DATA['durations'] as $durationData) {
            $duration = Duration::firstOrNew([
                'name' => $durationData['name'],
            ]);
            $duration->min = $durationData['min'];
            $duration->max = $durationData['max'];
            $duration->save();
        }

        // Locations
        foreach ($this::DATA['locations'] as $locationData) {
            $location = Location::firstOrNew([
                'name' => $locationData['name'],
            ]);
            $location->sort_order = $locationData['sort_order'];
            $location->save();
        }

        // Game Types
        foreach ($this::DATA['gameTypes'] as $gameTypeData) {
            $gameType = GameType::firstOrNew([
                'name' => $gameTypeData['name'],
            ]);
            $gameType->sort_order = $gameTypeData['sort_order'];
            $gameType->save();
        }

        // Forms
        foreach ($this::DATA['forms'] as $formData) {
            $form = Form::firstOrNew($formData);
            $form->save();
        }
        // Methodology Types

        foreach ($this::DATA['methodologyTypes'] as $methodologyTypeData) {
            $methodologyType = MethodologyType::firstOrNew([
                'name' => $methodologyTypeData['name'],
            ]);
            $methodologyType->sort_order = $methodologyTypeData['sort_order'];
            $methodologyType->save();
        }

        // Trial System Topic
        foreach ($this::DATA['trialSystemTopics'] as $trialSystemTopicData) {
            $trialSystemTopic = TrialSystemTopic::firstOrNew([
                'name' => $trialSystemTopicData,
            ]);
            $trialSystemTopic->save();
        }

        // Trial System Sub Topic
        foreach ($this::DATA['trialSystemSubTopics'] as $trialSystemSubTopicData) {
            $trialSystemSubTopic = TrialSystemSubTopic::firstOrNew([
                'name' => $trialSystemSubTopicData,
            ]);
            $trialSystemSubTopic->save();
        }

        // Trial System Type
        foreach ($this::DATA['trialSystemTypes'] as $trialSystemTypeData) {
            $trialSystemType = TrialSystemType::firstOrNew([
                'name' => $trialSystemTypeData,
            ]);
            $trialSystemType->save();
        }

        // Trial System Trial Type
        foreach ($this::DATA['trialSystemTrialTypes'] as $trialSystemTrialTypeData) {
            $trialSystemTrialType = TrialSystemTrialType::firstOrNew([
                'name' => $trialSystemTrialTypeData,
            ]);
            $trialSystemTrialType->save();
        }

        // Trial System Category
        foreach ($this::DATA['trialSystemCategories'] as $trialSystemCategoryData) {
            $trialSystemCategory = TrialSystemCategory::firstOrNew([
                'name' => $trialSystemCategoryData,
            ]);
            $trialSystemCategory->save();
        }

        // Song Type
        foreach ($this::DATA['songTypes'] as $songTypeData) {
            $songType = SongType::firstOrNew([
                'name' => $songTypeData
            ]);
            $songType->save();
        }

        // Folk Song type
        foreach ($this::DATA['folkSongTypes'] as $folkSongTypeData) {
            $folkSongType = FolkSongType::firstOrNew([
                'name' => $folkSongTypeData['name'],
            ]);
            $folkSongType->description = $folkSongTypeData['description'];
            $folkSongType->save();
        }

        // Region type
        foreach ($this::DATA['regions'] as $regionData) {
            $region = Region::firstOrNew([
                'name' => $regionData['name']
            ]);

            if (isset($regionData['big_parent'])) {
                $parent = Region::where('name', $regionData['big_parent'])->first() ?? null;
                $region->big_parent_id = $parent->id;
            }

            if (isset($regionData['mid_parent'])) {
                $parent = Region::where('name', $regionData['mid_parent'])->first() ?? null;
                $region->mid_parent_id = $parent->id;
            }

            if (isset($regionData['small_parent'])) {
                $parent = Region::where('name', $regionData['small_parent'])->first() ?? null;
                $region->small_parent_id = $parent->id;
            }

            $region->save();
        }

        // Folk song rhythms
        foreach ($this::DATA['folkSongRhythms'] as $folkSongRhythmData) {
            $folkSongRhythm = FolkSongRhythm::firstOrNew([
                'name' => $folkSongRhythmData['name'],
            ]);
            $folkSongRhythm->description = $folkSongRhythmData['description'];
            $folkSongRhythm->save();
        }
    }
}
