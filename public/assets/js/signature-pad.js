(() => {
  const canvas = document.getElementById('signatureCanvas');
  const input = document.getElementById('signatureInput');
  const clearBtn = document.getElementById('clearSignature');
  if (!canvas || !input) return;

  const ctx = canvas.getContext('2d');
  let drawing = false;
  let hasDrawn = false;
  let bounds = null;
  let resizeFrame = null;

  function setupContext() {
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#1a1a1a';
  }

  function resize() {
    const rect = canvas.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) return;

    const ratio = window.devicePixelRatio || 1;
    const data = hasDrawn ? canvas.toDataURL('image/png') : null;
    canvas.width = Math.max(1, Math.floor(rect.width * ratio));
    canvas.height = Math.max(1, Math.floor(rect.height * ratio));
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    setupContext();

    if (data) {
      const img = new Image();
      img.onload = () => {
        ctx.drawImage(img, 0, 0, rect.width, rect.height);
        saveSignature();
      };
      img.src = data;
    }
  }

  function resizeSoon() {
    if (resizeFrame) {
      window.cancelAnimationFrame(resizeFrame);
    }

    resizeFrame = window.requestAnimationFrame(() => {
      resizeFrame = null;
      resize();
    });
  }

  function ensureReady() {
    const rect = canvas.getBoundingClientRect();
    if (canvas.width > 0 && canvas.height > 0 && rect.width > 0 && rect.height > 0) {
      return true;
    }

    resize();
    return canvas.width > 0 && canvas.height > 0;
  }

  function pos(event) {
    const rect = canvas.getBoundingClientRect();
    return {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };
  }

  function updateBounds(point) {
    const pad = 10;
    if (!bounds) {
      bounds = {
        minX: point.x - pad,
        minY: point.y - pad,
        maxX: point.x + pad,
        maxY: point.y + pad,
      };
      return;
    }

    bounds.minX = Math.min(bounds.minX, point.x - pad);
    bounds.minY = Math.min(bounds.minY, point.y - pad);
    bounds.maxX = Math.max(bounds.maxX, point.x + pad);
    bounds.maxY = Math.max(bounds.maxY, point.y + pad);
  }

  function saveSignature() {
    if (!hasDrawn || !bounds) {
      input.value = '';
      return;
    }

    const ratio = window.devicePixelRatio || 1;
    const sourceX = Math.max(0, Math.floor(bounds.minX * ratio));
    const sourceY = Math.max(0, Math.floor(bounds.minY * ratio));
    const sourceMaxX = Math.min(canvas.width, Math.ceil(bounds.maxX * ratio));
    const sourceMaxY = Math.min(canvas.height, Math.ceil(bounds.maxY * ratio));
    const sourceW = Math.max(1, sourceMaxX - sourceX);
    const sourceH = Math.max(1, sourceMaxY - sourceY);
    const size = Math.max(sourceW, sourceH);
    const output = document.createElement('canvas');
    output.width = size;
    output.height = size;
    const out = output.getContext('2d');

    out.fillStyle = '#ffffff';
    out.fillRect(0, 0, output.width, output.height);
    out.drawImage(
      canvas,
      sourceX,
      sourceY,
      sourceW,
      sourceH,
      Math.floor((size - sourceW) / 2),
      Math.floor((size - sourceH) / 2),
      sourceW,
      sourceH,
    );

    input.value = output.toDataURL('image/png');
  }

  canvas.addEventListener('pointerdown', (event) => {
    if (!ensureReady()) {
      resizeSoon();
      return;
    }

    event.preventDefault();
    drawing = true;
    hasDrawn = true;
    if (canvas.setPointerCapture) {
      canvas.setPointerCapture(event.pointerId);
    }
    const point = pos(event);
    updateBounds(point);
    ctx.beginPath();
    ctx.moveTo(point.x, point.y);
  });

  canvas.addEventListener('pointermove', (event) => {
    if (!drawing) return;
    event.preventDefault();
    const point = pos(event);
    updateBounds(point);
    ctx.lineTo(point.x, point.y);
    ctx.stroke();
    saveSignature();
  });

  ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
    canvas.addEventListener(eventName, () => {
      drawing = false;
      saveSignature();
    });
  });

  clearBtn?.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    input.value = '';
    hasDrawn = false;
    bounds = null;
  });

  window.addEventListener('load', resizeSoon);
  window.addEventListener('pageshow', resizeSoon);
  window.addEventListener('resize', resizeSoon);
  document.addEventListener('visibilitychange', resizeSoon);
  canvas.addEventListener('mouseenter', resizeSoon);
  canvas.addEventListener('touchstart', resizeSoon, { passive: true });

  if ('ResizeObserver' in window) {
    new ResizeObserver(resizeSoon).observe(canvas);
  }

  resize();
  resizeSoon();
})();
