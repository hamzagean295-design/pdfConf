<x-app-layout>
<style>
    .container {
        margin-top: 0rem;
        margin-inline: auto;
    }
</style>
<script type="module">
  // If absolute URL from the remote server is provided, configure the CORS
  // header on that server.
  var url = '{{ $pdfUrl }}';
  var pageCount = '{{ $pageCount }}';
  pageCount = parseInt(pageCount);
  console.log(pageCount);

  // Loaded via <script> tag, create shortcut to access PDF.js exports.
  var { pdfjsLib } = globalThis;

  // The workerSrc property shall be specified.
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@5.4.530/build/pdf.worker.mjs';

  const Y_FROM_TOP = true;
  // ============================================

  var canvas = document.getElementById('the-canvas');
  const demarrer = document.getElementById('demarrer');
  const cordX = document.getElementById('cord-x');
  const cordY = document.getElementById('cord-y');
  let currentPage = document.getElementById('currentPage');
  const prevPage = document.getElementById('prevPage');
  const nextPage = document.getElementById('nextPage');
  const GLOBAL_SCALE = 1;

  let VIEWPORT;
  let VIEWPORT_ORIGINAL;
  let PDF_DOCUMENT;
  let currentPdfPage;
  var loadingTask = pdfjsLib.getDocument(url);
  var pageNumber = 1;

  function init() {
    loadingTask.promise.then(function(pdf) {
        console.log('PDF loaded');
        PDF_DOCUMENT = pdf;

        // Prepare canvas using PDF page dimensions
        pdf.getPage(pageNumber).then(function(page) {
            console.log('Page loaded');
            currentPdfPage = page;

            // Viewport avec scale = 1 (dimensions originales du PDF)
            var viewportOriginal = page.getViewport({scale: 1});
            VIEWPORT_ORIGINAL = viewportOriginal;

            // Viewport avec scale pour l'affichage
            var viewport = page.getViewport({scale: GLOBAL_SCALE});
            VIEWPORT = viewport;

            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);
            renderTask.promise.then(function () {
                console.log('Page rendered');
                console.log('Viewport (scaled):', VIEWPORT);
                console.log('Viewport (original):', VIEWPORT_ORIGINAL);
                console.log('Canvas size:', canvas.width, 'x', canvas.height);

                // Afficher la hauteur de la page
                const heightMm = (VIEWPORT_ORIGINAL.height * 25.4 / 72).toFixed(2);
                const pageHeightEl = document.getElementById('pageHeight');
                if (pageHeightEl) {
                    pageHeightEl.textContent = heightMm;
                }

                // Mettre à jour le texte d'indication
                const coordSystemEl = document.getElementById('coordSystem');
                if (coordSystemEl) {
                    coordSystemEl.textContent = Y_FROM_TOP ?
                        'Y=0 en haut de la page' :
                        'Y=0 en bas de la page (standard PDF)';
                }
            });
        });
    }, function (reason) {
          // PDF loading error
          console.error(reason);
    });

    currentPage.innerHTML = pageNumber;
  }

  // WARNING: start
  init();

  prevPage.addEventListener('click', function() {
    if(pageNumber <= 1) return;
    pageNumber-=1;
    init();
  });

  nextPage.addEventListener('click', function() {
    if(pageNumber >= pageCount) return;
    pageNumber+=1;
    init();
  });

  function convertToMm(xCanvas, yCanvas) {
      // Constante de conversion: 1 point PDF = 25.4/72 mm
      const MM_PER_POINT = 25.4 / 72;

      // 1. Convertir les coordonnées canvas en points PDF
      const xPoints = xCanvas / GLOBAL_SCALE;

      // Pour Y: choisir le système de coordonnées
      let yPoints;
      if (Y_FROM_TOP) {
          // Y depuis le haut de la page (0 = haut)
          yPoints = yCanvas / GLOBAL_SCALE;
      } else {
          // Y depuis le bas de la page (0 = bas) - Standard PDF
          yPoints = VIEWPORT_ORIGINAL.height - (yCanvas / GLOBAL_SCALE);
      }

      // 2. Convertir les points en millimètres
      const xMm = (xPoints * MM_PER_POINT) - 3; // 3 => marge d'erreur
      const yMm = yPoints * MM_PER_POINT;

      return {
          x: +xMm.toFixed(2),
          y: +yMm.toFixed(2),
      };
  }

  demarrer.addEventListener('click', function () {
      canvas.addEventListener('mousemove', updateCords);
      canvas.classList.add('cursor-crosshair');
  });


    function updateCords(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const xCanvasAdjusted = x * scaleX;
        const yCanvasAdjusted = y * scaleY;
        console.log(xCanvasAdjusted, yCanvasAdjusted);
        const cordos = convertToMm(xCanvasAdjusted, yCanvasAdjusted);
        cordX.value = cordos.x;
        cordY.value = cordos.y;

        cordX.dispatchEvent(new Event('input', { bubbles: true }));
        cordY.dispatchEvent(new Event('input', { bubbles: true }));
    }

  canvas.addEventListener('click', function() {
      canvas.classList.remove('cursor-crosshair');
      canvas.removeEventListener('mousemove', updateCords);
  })

</script>

<div class="container">
    <button id="demarrer" class="mb-8 mx-auto text-2xl border bg-blue-500 cursor-pointer text-white p-1 rounded-sm font-bold" type="button">démarrer</button>

    <div class="mb-4">
        <label class="block mb-2 font-semibold">Coordonnées X (mm):</label>
        <input type="text" name="x" id="cord-x" class="border p-2 w-full rounded" placeholder="coordonnée x" disabled>
    </div>
    <div class="mb-4">
        <label class="block mb-2 font-semibold">Coordonnées Y (mm):</label>
        <input type="text" name="y" id="cord-y" class="border p-2 w-full rounded" placeholder="coordonnée y" disabled>
    </div>
    <canvas id="the-canvas" class="border-2 border-gray-400 shadow-lg"></canvas>
    <div class="flex items-center justify-center gap-3 mt-4">
        <button type="button" class="border px-4 py-2 cursor-pointer rounded bg-gray-100 hover:bg-gray-200 font-medium" id="prevPage">← Précédente</button>
        <span class="text-xl font-bold mx-2">Page <span id="currentPage">1</span></span>
        <button class="border px-4 py-2 rounded cursor-pointer bg-gray-100 hover:bg-gray-200 font-medium" type="button" id="nextPage">Suivante →</button>
    </div>
    <div class="text-right mt-2 text-sm text-gray-600">
        <b>Dimensions originales: hauteur {{ $dimensionsPage['height'] }}px, largeur {{ $dimensionsPage['width'] }}px</b>
    </div>
</div>

</x-app-layout>
