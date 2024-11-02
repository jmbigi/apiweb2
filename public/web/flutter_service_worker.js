'use strict';
const MANIFEST = 'flutter-app-manifest';
const TEMP = 'flutter-temp-cache';
const CACHE_NAME = 'flutter-app-cache';

const RESOURCES = {"faristol_splash.jpg": "9ef58ec4a4956cd0e5b48586771410ad",
"manifest.json": "90c454c29b48f08bc06627d125f70466",
"main.dart.js_2.part.js": "be86e52b62a0c0e4b3ce23b094c1cef6",
"main.dart.js": "64621efec997a35d91f0dcbbd7cacb15",
"assets/shaders/ink_sparkle.frag": "ecc85a2e95f5e9f53123dcaf8cb9b6ce",
"assets/AssetManifest.bin.json": "5c229654d1283eacd5753b84433f822a",
"assets/AssetManifest.bin": "6c3f529bafc8dbbdc03ec9970a373785",
"assets/assets/googleplay.png": "dd688b12e4d5c974ffdf4ed298dcf96d",
"assets/assets/KrazyKat.pdf": "f11586e3aee8bccca338069a3b3a4e72",
"assets/assets/logo.webp": "a815566dc0c03e4e4d56be62e9f77765",
"assets/assets/images/decorations/viewPdfBackground.webp": "fa0cac29f9c7f3fa32a907680e19d5c9",
"assets/assets/images/decorations/viewPdfBackground.jpg": "cf866eb3fe28ad75068140489133826b",
"assets/assets/images/instruments/Snare.webp": "cf9eaa299275cbbf01d47b6bc0fda790",
"assets/assets/images/instruments/Bass%2520Clarinet.jpg": "e00357d454a254f0f0c14b59a1c0f44a",
"assets/assets/images/instruments/Timpani.jpg": "8fce42520615c820b738cba5011c73f2",
"assets/assets/images/instruments/Drum%2520Set.jpg": "0d48119fd371ecaabe877fda83a32e6e",
"assets/assets/images/instruments/Basson.jpg": "1e18badc48904b4f46a66327e37a2c2b",
"assets/assets/images/instruments/Basic.webp": "47007fb9383317b4ee672be956b581b2",
"assets/assets/images/instruments/Tuba.webp": "93a7c28ae5ce2b6910fff9c0a3e274a1",
"assets/assets/images/instruments/Viola.webp": "5b6869bc6863f7c54a6569b3db8e2fe5",
"assets/assets/images/instruments/Vibraphone.webp": "b83af5fa3c79dfa2a50a703c458af530",
"assets/assets/images/instruments/Flute.jpg": "30eac08acbc27fe832a6f83ddc7e6baa",
"assets/assets/images/instruments/Marimba.webp": "67310eeeb757fd4b77f053528d12bd7d",
"assets/assets/images/instruments/Marimba.jpg": "e6b3591b41c6c7bf3673b43ff12655c6",
"assets/assets/images/instruments/Saxophone.jpg": "2f61962405d312de5dbd046e4ead276f",
"assets/assets/images/instruments/Baritone%2520Saxophone.jpg": "96e8e65e2f7f531ad15053639ac74356",
"assets/assets/images/instruments/Piano.jpg": "22eee66d2bad97d103d7b7e530a9d69f",
"assets/assets/images/instruments/Basson.webp": "03986c6b8065d3cc54761a53a1c6a6f3",
"assets/assets/images/instruments/Guitar.jpg": "16d735c544dfb780c9ae82a1b3d20176",
"assets/assets/images/instruments/Trumpet.webp": "6d9c2f644112dec2fb05b55699659789",
"assets/assets/images/instruments/Cello.jpg": "5cb56b0bab66e8ee65890e6b304fca0f",
"assets/assets/images/instruments/Oboe.jpg": "60f066f23553f1bea63c84bc4a80fa91",
"assets/assets/images/instruments/Trumpet.jpg": "42ee8db5e350d8eeb5f30b709d9f167b",
"assets/assets/images/instruments/Vibraphone.jpg": "95ae3d50a71a1c2d14551ed6a2682e6b",
"assets/assets/images/instruments/Viola.jpg": "8a744bdc65dd0fbd04e902a92e8eda34",
"assets/assets/images/instruments/Alto%2520Saxophone.jpg": "2f61962405d312de5dbd046e4ead276f",
"assets/assets/images/instruments/Multipercussion.jpg": "610ea280d501f934080d0977798626bb",
"assets/assets/images/instruments/Tuba.jpg": "4a93f472c39233a5fe25111cd3a8cf15",
"assets/assets/images/instruments/Contrabass.webp": "e3e6e2c880aea3d0855cac1659de5db4",
"assets/assets/images/instruments/Piccolo.webp": "ba17056ee79bf4d4f58746de19f0f341",
"assets/assets/images/instruments/Basic.jpg": "7240f4105b15142c62a8023964bd4b4a",
"assets/assets/images/instruments/Violin.webp": "fd1aeda6893f388a91474fd4fd95986c",
"assets/assets/images/instruments/Timpani.webp": "ccceb1203347f8c1e92aa007f24cb04e",
"assets/assets/images/instruments/Saxophone.webp": "1f7495fc7dd0c9ceeabed0919351bef9",
"assets/assets/images/instruments/French%2520Horn.webp": "6094bbc328bbede6043f005a40f7e6d5",
"assets/assets/images/instruments/Oboe.webp": "e03d55d16cc908afb574f2ab53b24714",
"assets/assets/images/instruments/Arpa.jpg": "5ae60c4c3eaac30413459571052850a4",
"assets/assets/images/instruments/Flute.webp": "0645b555d097e32b3a1f632943a57252",
"assets/assets/images/instruments/Trombone.webp": "24e315bca52c0b149342dcf0fe3f4ce6",
"assets/assets/images/instruments/Clarinet.jpg": "e00357d454a254f0f0c14b59a1c0f44a",
"assets/assets/images/instruments/Multipercussion.webp": "b29b1f5bffa63ac48d44a7c476f786f8",
"assets/assets/images/instruments/Piccolo.jpg": "8893cb37eb9be2c0b9a0daf07bf1bd3a",
"assets/assets/images/instruments/Arpa.webp": "aaefdc8c4033ad881c445947ff84ed49",
"assets/assets/images/instruments/Euphonium.webp": "7ce01c53b1576c410f2b5289647fddb5",
"assets/assets/images/instruments/Guitar.webp": "a4678effca34f7825731be37b9db1585",
"assets/assets/images/instruments/Contrabass.jpg": "69974085d14339c9e4343c3c77448ad3",
"assets/assets/images/instruments/Trombone.jpg": "77b6dd6cad8a62f6b025a34165ec59d7",
"assets/assets/images/instruments/Baritone%2520Saxophone.webp": "5404c91e252565d5f64b87e49c84e702",
"assets/assets/images/instruments/French%2520Horn.jpg": "6db2437e883d0718f72fd5f8699201e9",
"assets/assets/images/instruments/Bass%2520Clarinet.webp": "e5d4404f81c385343c85b830e12bc194",
"assets/assets/images/instruments/Alto%2520Saxophone.webp": "1f7495fc7dd0c9ceeabed0919351bef9",
"assets/assets/images/instruments/Euphonium.jpg": "648143ea478c6ad1636a310df6442c07",
"assets/assets/images/instruments/Violin.jpg": "ddd03634902a52e0b42edda92d83ac08",
"assets/assets/images/instruments/Snare.jpg": "5b166c82992a6d790cd73c2c68a5fe65",
"assets/assets/images/instruments/Cello.webp": "6ee2ff5b851612fc97159e8732b4220d",
"assets/assets/images/instruments/Drum%2520Set.webp": "07e5f945d5cc132169903acb65941796",
"assets/assets/images/instruments/Clarinet.webp": "e5d4404f81c385343c85b830e12bc194",
"assets/assets/images/instruments/Piano.webp": "e53d40c8ce7163a23cb2faa121c3782f",
"assets/assets/images/musicStyles/Serialism.jpg": "2e6c6c1e1675ada234b24279fbca755d",
"assets/assets/images/musicStyles/Romanticism.jpg": "97faa8aa8d35e2e1479360eb3718628a",
"assets/assets/images/musicStyles/Jazz.webp": "a432ee3507ede7d4acb847cfb129fc39",
"assets/assets/images/musicStyles/Classicism.jpg": "b0f8804c22e64ab0554f62fac31ffeb8",
"assets/assets/images/musicStyles/Party%2520Music.jpg": "9312356ae26ee5ef0b55c1c4ee281510",
"assets/assets/images/musicStyles/Basic.webp": "ee3fb97e4d8f17f5af7e1603c32f0379",
"assets/assets/images/musicStyles/Minimalism.webp": "83d6bf513eb24c2be9d8313f60be7d8c",
"assets/assets/images/musicStyles/Rock.webp": "299b4b0d6ebbfa5fecb575e9fba4b1d4",
"assets/assets/images/musicStyles/Blues.jpg": "a2da4a87f893c388e5063e035ce23b87",
"assets/assets/images/musicStyles/Blues.webp": "27e832de9a1184111ba453b6a0559caa",
"assets/assets/images/musicStyles/Jazz.jpg": "855a8b8311866d0c4a2755313b6b32a1",
"assets/assets/images/musicStyles/Impressionism.webp": "c9b848c0faa2d32e1801623f6f8309e3",
"assets/assets/images/musicStyles/Street%2520Music.webp": "d59f1ecf53d21c32cfc8102544558b9e",
"assets/assets/images/musicStyles/Neoclassicism.webp": "0ef583be3ca17a1b479910d32fdf8b6c",
"assets/assets/images/musicStyles/Romanticism.webp": "6e4ded8d6219542c9b2df1f9dcfce1f6",
"assets/assets/images/musicStyles/Street%2520Music.jpg": "6726e60e5b2cc32f1c315e381405cf63",
"assets/assets/images/musicStyles/Nationalism.jpg": "87bfa486f2f6bf030f3fd82834a539ae",
"assets/assets/images/musicStyles/Atonalism.jpg": "b47ddcbed67c46cb3752825aca1c00c4",
"assets/assets/images/musicStyles/Soul.webp": "d3ad2dd14c3e68e258dbf3c2210f1f84",
"assets/assets/images/musicStyles/Dodecaphonism.webp": "ac773b332bd68e86f2f292e7feb79f99",
"assets/assets/images/musicStyles/Reggae.webp": "18de1a4c670b6a1db4b083a089fc232b",
"assets/assets/images/musicStyles/Basic.jpg": "7240f4105b15142c62a8023964bd4b4a",
"assets/assets/images/musicStyles/Neoclassicism.jpg": "27a9e3311aa3f0546ed641fba2e1d581",
"assets/assets/images/musicStyles/Folk.jpg": "4f4e682527911dd6ae017d056becc88e",
"assets/assets/images/musicStyles/Country%2520Music.jpg": "fc36920fbcce8576de568f424b19fb9c",
"assets/assets/images/musicStyles/Classicism.webp": "824444396c9997d0e2f18f8afa83f091",
"assets/assets/images/musicStyles/Serialism.webp": "8a6456b2cfacda9b19cf4f53818578c0",
"assets/assets/images/musicStyles/Reggae.jpg": "deae05115e3bd19fa633f985c99917f2",
"assets/assets/images/musicStyles/Nationalism.webp": "9ba814d97b96bd33fbd6b004bdb7e1ce",
"assets/assets/images/musicStyles/Baroque.jpg": "0b1e3af8e9a36f44fc34598494c54f76",
"assets/assets/images/musicStyles/Rock.jpg": "2f97a2fe37745056a5d99470a86643f6",
"assets/assets/images/musicStyles/Soul.jpg": "2661a881f97b390c7f7a258459826668",
"assets/assets/images/musicStyles/Postromanticism.webp": "3a34da4642a30bd9af17b7388cb29805",
"assets/assets/images/musicStyles/Impressionism.jpg": "0162f82a2364f6b2a63dd4ab892a68d3",
"assets/assets/images/musicStyles/Minimalism.jpg": "ba0d5f9c7f089d9e2daec27ccc3254d3",
"assets/assets/images/musicStyles/Postromanticism.jpg": "83ef4335892a6dc83a9b45b1af2aac71",
"assets/assets/images/musicStyles/Atonalism.webp": "6e3813e10acb3c612bc74a5f53e753c4",
"assets/assets/images/musicStyles/Folk.webp": "fa433046b65a6e810b800df25e32e532",
"assets/assets/images/musicStyles/Dodecaphonism.jpg": "05f6de2971a1f5c6d9be689c3a7659ab",
"assets/assets/images/musicStyles/Party%2520Music.webp": "b2e9d5eec1e0c7832a385f8acd290e0d",
"assets/assets/images/musicStyles/Baroque.webp": "951663d0ed42fa305f7d74a30cacb834",
"assets/assets/appstore.png": "fed3a2d3c75337ab9b1769f8243bded7",
"assets/assets/logotipo.webp": "0efe161c7f71d6a228c1240ad94864cc",
"assets/assets/sample.pdf": "1c439050d918b64909ae5ec1a26edc9f",
"assets/assets/icons/musicpage2.png": "151eeb7d28efd5425c999dee4ffc9ab5",
"assets/assets/icons/musicStyleRomanticism.png": "50c0d70cace0920dca1b24e8f8ded309",
"assets/assets/icons/musicStyleRomanticism.webp": "967feaaeaeffd6ce95bd4b0450028859",
"assets/assets/icons/musicstyle0.png": "a45773208422eff898234c740448775b",
"assets/assets/icons/favoriteicon.png": "b602f8e9b2381026e3c496dc1e22c6ea",
"assets/assets/icons/viewallicon.png": "c83c5030bb7fbac7a43741342208aeef",
"assets/assets/icons/eraser-svgrepo-com.svg": "9385ad218be0c5c6f569dc7802df4836",
"assets/assets/icons/favorite_no.png": "b602f8e9b2381026e3c496dc1e22c6ea",
"assets/assets/icons/music.png": "df9b9d1428310f62621d860e3235f4ca",
"assets/assets/icons/cross_icon.png": "2861e45fa896e9d6f84083073ab7918c",
"assets/assets/icons/splashbackground.png": "b1e2fb67ff3738a8719045b045859c73",
"assets/assets/icons/pen-line-svgrepo-com.svg": "4a215c738e8add13e6def6d9f6988e03",
"assets/assets/icons/musiclogin.png": "9123a67792f14d8910b7c55238219fc9",
"assets/assets/icons/paint-brush-solid-svgrepo-com.svg": "b52d6097dcf6e47393f684cd15d4c472",
"assets/assets/icons/musicStyleNationalism.png": "64fbb475941b116c40e2bf063b2c28d6",
"assets/assets/icons/ic_music_score_edit.png": "8ce9807ccb0b03e227cbcd6b4c474fcf",
"assets/assets/icons/right_tick.png": "8839e64c1cd123caa0bc6fca82d5213a",
"assets/assets/icons/ic_music_score_statistic.png": "3c7d0c1669f370ebfe19139a3408a5d5",
"assets/assets/icons/classicmusic1.png": "85b8345e58d809d23649deedc33c285c",
"assets/assets/icons/musicicon2.png": "2879ba7c24a431f66b8ccd3e502fcfa0",
"assets/assets/icons/downloadicon.png": "ce58977706069ba2a7763fa53384cb63",
"assets/assets/icons/successfull.png": "1e26c6b824cc9e3b85308a3583ea74ba",
"assets/assets/icons/viewallicon.webp": "107bf9f1b3d19d7f89880fe99915c935",
"assets/assets/icons/undo-svgrepo-com.svg": "b5ccd5f4c46f408edf20a390da12e4c0",
"assets/assets/icons/ic_music_list_cover_icon.svg": "ba7d5e496a2b1674c4baf938722eaf51",
"assets/assets/icons/borrar.png": "9f2996cfbbc6f22a9b936ade8eef2eba",
"assets/assets/icons/splashborderline.png": "35c723d379e497e1ec653064d2a357db",
"assets/assets/icons/hotnewicon.png": "8ea1481266e00c02d865d88020e9e4cd",
"assets/assets/icons/profileicon.png": "f854bbed7ff65b18c8fc331d3a832b78",
"assets/assets/icons/rockmusic1.png": "7c5b8aaddd1917669f85356f6f344307",
"assets/assets/icons/downloadiconred.png": "b0ff71178428b561b23e7093203446be",
"assets/assets/icons/drawerheaderimage.png": "b869821501dfab4341af515a9de36e0f",
"assets/assets/icons/ic_music_list_cover_icon.png": "ff0e9b42c457cce1e2adc476fa3f494b",
"assets/assets/icons/musicStyleJazz.png": "e8228fd62432e4276be120f314c44c32",
"assets/assets/icons/line-thickness-solid-svgrepo-com.svg": "ba81913af8d1a1845eb188d45dd1514a",
"assets/assets/icons/musicpage1.png": "a45773208422eff898234c740448775b",
"assets/assets/icons/faristol.png": "adbf49dab62b0fb348661789da5664c5",
"assets/assets/icons/ic_music_score_view.webp": "37d26266c81723f71d00d3bde8731cfd",
"assets/assets/icons/spinner.png": "e1317ffef59e4075eaaa65248b7b4f09",
"assets/assets/icons/musicStyleJazz.webp": "63729f8bdce02bf726572a26c7a36e07",
"assets/assets/icons/infoicon.png": "828c3dd0bf7726be20f5d8267155947b",
"assets/assets/icons/musicStyleBaroque.webp": "2001947551452bfdc53dd8a6b0abcea7",
"assets/assets/icons/musicStyleBaroque.png": "e83ddca26a83b570067846a4c7a1bdae",
"assets/assets/icons/ic_music_score_view.png": "78562c6c4e062bfa8690a4bf3d29b814",
"assets/assets/icons/rockmusic111.png": "51e366a23e0f809c1d3a7bff5cbb2582",
"assets/assets/icons/save-svgrepo-com.svg": "0d29c0fdda9baa908bb45129a8fbabc4",
"assets/assets/icons/favorite_yes.png": "5aab7092c3e1891c5dfbd53753b8587c",
"assets/assets/icons/color-palette-svgrepo-com.svg": "c44ddb599e278de7a60f8f53992eee84",
"assets/assets/icons/reset-reload-refresh-sync-arrow-update-svgrepo-com.svg": "6f1d7752da70c4cfa966910f5e670b20",
"assets/assets/icons/musicicon2.webp": "214ca44901307dfcdb664cbe8f1d9280",
"assets/assets/icons/musicStyleClassicism.png": "44e080aab799c620664565da02d3402f",
"assets/assets/icons/favoritredicon.png": "d5aac4ad18916832c7e8db11b3247caa",
"assets/assets/icons/maskgroup.png": "d8ae80d76eb29e291882e8e91fd846ea",
"assets/assets/icons/uploadfile.png": "64d02efd34a6c471f9d0305cd10f4b2b",
"assets/assets/icons/appbarfaristol.png": "89ba26ced6c40f7d368cb40b9caddf11",
"assets/assets/icons/musicStyleNationalism.webp": "6bb1162cef3c1dff51bad07a24596385",
"assets/assets/icons/musicStyleClassicism.webp": "8e1462de81e62943b067863e9604cab5",
"assets/AssetManifest.json": "1dfbf80a635a191c4c6b04b99335567a",
"assets/NOTICES": "918b2811983c3dc2100fa10f94bef6ac",
"assets/fonts/MaterialIcons-Regular.otf": "789c8fc8272457515671b9521836f7c5",
"assets/FontManifest.json": "dc3d03800ccca4601324923c0b1d6d57",
"assets/packages/fluttertoast/assets/toastify.css": "a85675050054f179444bc5ad70ffc635",
"assets/packages/fluttertoast/assets/toastify.js": "56e2c9cedd97f10e7e5f1cebd85d53e3",
"assets/packages/cupertino_icons/assets/CupertinoIcons.ttf": "e986ebe42ef785b27164c36a9abc7818",
"main.dart.js_1.part.js": "43d6fbe5f58fefd86fe7815f78642f26",
"canvaskit/skwasm.worker.js": "89990e8c92bcb123999aa81f7e203b1c",
"canvaskit/skwasm.js": "694fda5704053957c2594de355805228",
"canvaskit/skwasm.js.symbols": "262f4827a1317abb59d71d6c587a93e2",
"canvaskit/canvaskit.js": "66177750aff65a66cb07bb44b8c6422b",
"canvaskit/canvaskit.wasm": "1f237a213d7370cf95f443d896176460",
"canvaskit/skwasm.wasm": "9f0c0c02b82a910d12ce0543ec130e60",
"canvaskit/chromium/canvaskit.js": "671c6b4f8fcc199dcc551c7bb125f239",
"canvaskit/chromium/canvaskit.wasm": "b1ac05b29c127d86df4bcfbf50dd902a",
"canvaskit/chromium/canvaskit.js.symbols": "a012ed99ccba193cf96bb2643003f6fc",
"canvaskit/canvaskit.js.symbols": "48c83a2ce573d9692e8d970e288d75f7",
"version.json": "980547175e325fe622a3362b84d55b6a",
"main.dart.js_3.part.js": "be32cd70864f740b34986817b1761eb0",
"index2.html": "0f55b11ea4eab38f0f27c428cb2aadaa",
"flutter_bootstrap.js": "a035d240d5aaea29a4a74460275fda13",
"caching_server.py": "97c9c6e60398d4de58e0347ad5d73f39",
"index.html": "84303955d1818eab4ae06bbd61984f28",
"/": "84303955d1818eab4ae06bbd61984f28",
"favicon.png": "34fcda3862cb34f04f28da6877b207c8",
"ic_launcher.png": "34fcda3862cb34f04f28da6877b207c8",
"icons/Icon-192.png": "4ffe424ed98b3a047a44b550a4d72abd",
"icons/Icon-512.png": "846d9696f024895493c734424df1a2fd",
"icons/Icon-maskable-512.png": "87933417a02ee77d7aa9d3c9c56c12c1",
"icons/Icon-maskable-192.png": "d5ad5b31804c41b8bbc17db6faa40cb5",
"flutter.js": "f393d3c16b631f36852323de8e583132"};
// The application shell files that are downloaded before a service worker can
// start.
const CORE = ["main.dart.js",
"index.html",
"flutter_bootstrap.js",
"assets/AssetManifest.bin.json",
"assets/FontManifest.json"];

// During install, the TEMP cache is populated with the application shell files.
self.addEventListener("install", (event) => {
  self.skipWaiting();
  return event.waitUntil(
    caches.open(TEMP).then((cache) => {
      return cache.addAll(
        CORE.map((value) => new Request(value, {'cache': 'reload'})));
    })
  );
});
// During activate, the cache is populated with the temp files downloaded in
// install. If this service worker is upgrading from one with a saved
// MANIFEST, then use this to retain unchanged resource files.
self.addEventListener("activate", function(event) {
  return event.waitUntil(async function() {
    try {
      var contentCache = await caches.open(CACHE_NAME);
      var tempCache = await caches.open(TEMP);
      var manifestCache = await caches.open(MANIFEST);
      var manifest = await manifestCache.match('manifest');
      // When there is no prior manifest, clear the entire cache.
      if (!manifest) {
        await caches.delete(CACHE_NAME);
        contentCache = await caches.open(CACHE_NAME);
        for (var request of await tempCache.keys()) {
          var response = await tempCache.match(request);
          await contentCache.put(request, response);
        }
        await caches.delete(TEMP);
        // Save the manifest to make future upgrades efficient.
        await manifestCache.put('manifest', new Response(JSON.stringify(RESOURCES)));
        // Claim client to enable caching on first launch
        self.clients.claim();
        return;
      }
      var oldManifest = await manifest.json();
      var origin = self.location.origin;
      for (var request of await contentCache.keys()) {
        var key = request.url.substring(origin.length + 1);
        if (key == "") {
          key = "/";
        }
        // If a resource from the old manifest is not in the new cache, or if
        // the MD5 sum has changed, delete it. Otherwise the resource is left
        // in the cache and can be reused by the new service worker.
        if (!RESOURCES[key] || RESOURCES[key] != oldManifest[key]) {
          await contentCache.delete(request);
        }
      }
      // Populate the cache with the app shell TEMP files, potentially overwriting
      // cache files preserved above.
      for (var request of await tempCache.keys()) {
        var response = await tempCache.match(request);
        await contentCache.put(request, response);
      }
      await caches.delete(TEMP);
      // Save the manifest to make future upgrades efficient.
      await manifestCache.put('manifest', new Response(JSON.stringify(RESOURCES)));
      // Claim client to enable caching on first launch
      self.clients.claim();
      return;
    } catch (err) {
      // On an unhandled exception the state of the cache cannot be guaranteed.
      console.error('Failed to upgrade service worker: ' + err);
      await caches.delete(CACHE_NAME);
      await caches.delete(TEMP);
      await caches.delete(MANIFEST);
    }
  }());
});
// The fetch handler redirects requests for RESOURCE files to the service
// worker cache.
self.addEventListener("fetch", (event) => {
  if (event.request.method !== 'GET') {
    return;
  }
  var origin = self.location.origin;
  var key = event.request.url.substring(origin.length + 1);
  // Redirect URLs to the index.html
  if (key.indexOf('?v=') != -1) {
    key = key.split('?v=')[0];
  }
  if (event.request.url == origin || event.request.url.startsWith(origin + '/#') || key == '') {
    key = '/';
  }
  // If the URL is not the RESOURCE list then return to signal that the
  // browser should take over.
  if (!RESOURCES[key]) {
    return;
  }
  // If the URL is the index.html, perform an online-first request.
  if (key == '/') {
    return onlineFirst(event);
  }
  event.respondWith(caches.open(CACHE_NAME)
    .then((cache) =>  {
      return cache.match(event.request).then((response) => {
        // Either respond with the cached resource, or perform a fetch and
        // lazily populate the cache only if the resource was successfully fetched.
        return response || fetch(event.request).then((response) => {
          if (response && Boolean(response.ok)) {
            cache.put(event.request, response.clone());
          }
          return response;
        });
      })
    })
  );
});
self.addEventListener('message', (event) => {
  // SkipWaiting can be used to immediately activate a waiting service worker.
  // This will also require a page refresh triggered by the main worker.
  if (event.data === 'skipWaiting') {
    self.skipWaiting();
    return;
  }
  if (event.data === 'downloadOffline') {
    downloadOffline();
    return;
  }
});
// Download offline will check the RESOURCES for all files not in the cache
// and populate them.
async function downloadOffline() {
  var resources = [];
  var contentCache = await caches.open(CACHE_NAME);
  var currentContent = {};
  for (var request of await contentCache.keys()) {
    var key = request.url.substring(origin.length + 1);
    if (key == "") {
      key = "/";
    }
    currentContent[key] = true;
  }
  for (var resourceKey of Object.keys(RESOURCES)) {
    if (!currentContent[resourceKey]) {
      resources.push(resourceKey);
    }
  }
  return contentCache.addAll(resources);
}
// Attempt to download the resource online before falling back to
// the offline cache.
function onlineFirst(event) {
  return event.respondWith(
    fetch(event.request).then((response) => {
      return caches.open(CACHE_NAME).then((cache) => {
        cache.put(event.request, response.clone());
        return response;
      });
    }).catch((error) => {
      return caches.open(CACHE_NAME).then((cache) => {
        return cache.match(event.request).then((response) => {
          if (response != null) {
            return response;
          }
          throw error;
        });
      });
    })
  );
}
