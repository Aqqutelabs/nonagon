(() => {
  const trigger = document.querySelector('[data-share-listing]');
  const modal = document.querySelector('#share-card-modal');
  const canvas = document.querySelector('#share-card-canvas');
  const source = document.querySelector('#share-card-data');
  if (!trigger || !modal || !canvas || !source) return;

  const data = JSON.parse(source.textContent);
  const context = canvas.getContext('2d');
  const companyUrl = new URL(data.company_path, document.baseURI).href;
  const equipmentUrl = new URL(data.equipment_path, document.baseURI).href;
  const companyInput = document.querySelector('#share-company-link');
  const equipmentInput = document.querySelector('#share-equipment-link');
  companyInput.value = companyUrl;
  equipmentInput.value = equipmentUrl;

  const image = new Image();
  image.decoding = 'async';
  image.src = data.image;
  let rendered = false;

  const roundedRect = (x, y, width, height, radius) => {
    context.beginPath();
    context.roundRect(x, y, width, height, radius);
  };

  const coverImage = (img, x, y, width, height) => {
    const scale = Math.max(width / img.naturalWidth, height / img.naturalHeight);
    const sourceWidth = width / scale;
    const sourceHeight = height / scale;
    const sourceX = (img.naturalWidth - sourceWidth) / 2;
    const sourceY = (img.naturalHeight - sourceHeight) / 2;
    context.save();
    roundedRect(x, y, width, height, 15);
    context.clip();
    context.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, x, y, width, height);
    context.restore();
  };

  const wrapText = (text, x, y, maxWidth, lineHeight, maxLines = 2) => {
    const words = String(text).split(/\s+/);
    const lines = [];
    let line = '';
    for (const word of words) {
      const attempt = line ? `${line} ${word}` : word;
      if (context.measureText(attempt).width > maxWidth && line) {
        lines.push(line);
        line = word;
        if (lines.length === maxLines - 1) break;
      } else line = attempt;
    }
    if (lines.length < maxLines && line) lines.push(line);
    lines.forEach((value, index) => context.fillText(value, x, y + index * lineHeight));
    return y + lines.length * lineHeight;
  };

  const drawQrPlaceholder = (x, y, size) => {
    context.fillStyle = '#fff';
    context.fillRect(x, y, size, size);
    const cells = 25;
    const cell = size / cells;
    const finder = (column, row) => {
      context.fillStyle = '#102d50';
      context.fillRect(x + column * cell, y + row * cell, 7 * cell, 7 * cell);
      context.fillStyle = '#fff';
      context.fillRect(x + (column + 1) * cell, y + (row + 1) * cell, 5 * cell, 5 * cell);
      context.fillStyle = '#102d50';
      context.fillRect(x + (column + 2) * cell, y + (row + 2) * cell, 3 * cell, 3 * cell);
    };
    for (let row = 0; row < cells; row += 1) {
      for (let column = 0; column < cells; column += 1) {
        const inFinder = (column < 8 && row < 8) || (column > 16 && row < 8) || (column < 8 && row > 16);
        if (!inFinder && ((column * 7 + row * 11 + column * row) % 5 < 2)) {
          context.fillStyle = '#102d50';
          context.fillRect(x + column * cell, y + row * cell, Math.ceil(cell), Math.ceil(cell));
        }
      }
    }
    finder(0, 0); finder(18, 0); finder(0, 18);
    context.fillStyle = '#fff';
    context.beginPath();
    context.arc(x + size / 2, y + size / 2, 32, 0, Math.PI * 2);
    context.fill();
    context.strokeStyle = '#a71930';
    context.lineWidth = 7;
    context.beginPath();
    context.arc(x + size / 2, y + size / 2, 20, .3, Math.PI * 1.75);
    context.stroke();
    context.fillStyle = '#a71930';
    context.font = '700 13px Arial';
    context.textAlign = 'center';
    context.fillText('QR PLACEHOLDER', x + size / 2, y + size + 20);
  };

  const draw = async () => {
    if (!image.complete) await image.decode();
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.fillStyle = '#fff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.textAlign = 'left';
    context.fillStyle = '#111';
    context.font = '700 38px Arial';
    const titleBottom = wrapText(data.title, 24, 48, 572, 43, 2);
    const imageTop = Math.max(124, titleBottom + 12);
    coverImage(image, 24, imageTop, 572, 450);

    const tableTop = imageTop + 480;
    const entries = [['Rate', data.price], ['Location', data.location], ...Object.entries(data.specs || {})].slice(0, 6);
    const rowHeight = 38;
    context.fillStyle = '#f4f5f7';
    roundedRect(298, tableTop - 13, 298, entries.length * rowHeight + 18, 14);
    context.fill();
    context.fillStyle = '#111';
    context.font = '16px Arial';
    entries.forEach(([label, value], index) => {
      const y = tableTop + index * rowHeight + 13;
      context.fillStyle = '#111';
      context.fillText(`${label}:`, 40, y);
      context.fillStyle = index === 0 ? '#172554' : '#26313d';
      context.fillText(String(value || 'Not specified'), 314, y);
    });

    const qrSize = 190;
    const qrY = Math.min(820, tableTop + entries.length * rowHeight + 52);
    context.fillStyle = '#17233b';
    context.font = '17px Arial';
    context.textAlign = 'center';
    context.fillText('SCAN TO VIEW EQUIPMENT', 310, qrY - 14);
    drawQrPlaceholder(215, qrY, qrSize);
    context.fillStyle = '#a71930';
    context.font = '18px Arial';
    context.fillText(`${location.host}/company/${data.company_path.split('/').pop()}`, 310, qrY + qrSize + 47);
    context.fillStyle = '#102d50';
    context.font = '700 21px Arial';
    context.fillText('NONAGON', 310, qrY + qrSize + 82);
    context.fillStyle = '#657384';
    context.font = '12px Arial';
    context.fillText('Equipment. People. Operations.', 310, qrY + qrSize + 101);
    context.textAlign = 'left';
    rendered = true;
  };

  const download = (blob, filename) => {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
  };

  const jpegBlob = () => new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', .94));

  const pdfFromJpeg = async () => {
    const jpeg = new Uint8Array(await (await jpegBlob()).arrayBuffer());
    const encoder = new TextEncoder();
    const chunks = [];
    const offsets = [0];
    let length = 0;
    const add = value => { const bytes = typeof value === 'string' ? encoder.encode(value) : value; chunks.push(bytes); length += bytes.length; };
    add('%PDF-1.4\n');
    const object = (number, content) => { offsets[number] = length; add(`${number} 0 obj\n`); add(content); add('\nendobj\n'); };
    object(1, '<< /Type /Catalog /Pages 2 0 R >>');
    object(2, '<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
    object(3, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 620 1200] /Resources << /XObject << /Card 4 0 R >> >> /Contents 5 0 R >>');
    offsets[4] = length; add(`4 0 obj\n<< /Type /XObject /Subtype /Image /Width 620 /Height 1200 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${jpeg.length} >>\nstream\n`); add(jpeg); add('\nendstream\nendobj\n');
    const commands = 'q 620 0 0 1200 0 0 cm /Card Do Q';
    object(5, `<< /Length ${commands.length} >>\nstream\n${commands}\nendstream`);
    const xref = length;
    add('xref\n0 6\n0000000000 65535 f \n');
    for (let index = 1; index <= 5; index += 1) add(`${String(offsets[index]).padStart(10, '0')} 00000 n \n`);
    add(`trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF`);
    return new Blob(chunks, { type: 'application/pdf' });
  };

  trigger.addEventListener('click', async () => {
    modal.showModal();
    if (!rendered) {
      try { await draw(); } catch { context.fillText('Equipment card preview unavailable.', 30, 50); }
    }
  });
  modal.querySelector('[data-share-close]').addEventListener('click', () => modal.close());
  modal.addEventListener('click', event => { if (event.target === modal) modal.close(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal.open) modal.close(); });
  modal.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    const value = button.dataset.copy === 'company' ? companyUrl : equipmentUrl;
    await navigator.clipboard.writeText(value);
    const previous = button.textContent;
    button.textContent = 'Copied';
    setTimeout(() => { button.textContent = previous; }, 1400);
  }));
  modal.querySelector('[data-download="jpeg"]').addEventListener('click', async () => download(await jpegBlob(), `equipment-${data.company_path.split('/').pop()}.jpg`));
  modal.querySelector('[data-download="pdf"]').addEventListener('click', async () => download(await pdfFromJpeg(), `equipment-${data.company_path.split('/').pop()}.pdf`));
})();
