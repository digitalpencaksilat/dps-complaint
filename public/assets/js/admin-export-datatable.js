/**
 * DataTable Export Helper for DPS Complain admin tables.
 * Adapted from DPS CI4 export helper. Print header intentionally text-only.
 */
;(function () {
  'use strict';

  if (!window.jQuery) return;

  var $ = window.jQuery;

  function initFallbackDataTable(selector, options) {
    return $(selector).DataTable(
      Object.assign(
        {
          responsive: false,
          autoWidth: false,
          pageLength: 10,
          language: {
            search: '',
            searchPlaceholder: 'Cari data...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada data',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
          },
        },
        options,
      ),
    );
  }

  var presets = {
    'simple-list': { fontSize: '10px', tableWidth: '100%', orientation: 'portrait' },
    'wide-report': { fontSize: '9px', tableWidth: '95%', orientation: 'landscape' },
    'summary-table': { fontSize: '10px', tableWidth: '100%', orientation: 'portrait' },
  };

  function normalizeEmptyBodyRows(selector) {
    $(selector).each(function () {
      var table = this;
      var columnCount = $(table).find('thead th').length;
      if (!columnCount) return;

      $(table)
        .find('tbody tr')
        .each(function () {
          var $row = $(this);
          var cells = $row.children('td, th');
          if (cells.length !== 1) return;

          var $cell = cells.eq(0);
          var colspan = parseInt($cell.attr('colspan') || '1', 10);
          if (colspan === columnCount) return;

          $cell.attr('colspan', columnCount);
        });
    });
  }

  function syncExternalEmptyMessage(selector, tableApi) {
    var $message = $(selector).closest('.admin-table-wrap').find('.datatable-empty-message');
    if (!$message.length) return;

    $message.toggle(tableApi.rows().data().length === 0);
  }

  window.dpsReportPrintCustomize = function (win, opts) {
    opts = opts || {};
    var $win = $(win.document);

    $win.find('head').append(
      '<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">' +
        '<style>' +
        'body{font-family:\'Poppins\',Arial,sans-serif!important;font-size:11px;color:#212529;}' +
        '.dps-print-header{text-align:center;margin:0 0 16px 0;padding:0 0 12px 0;border-bottom:3px solid #c60000;background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        '.dps-print-header-title{font-family:\'Oswald\',\'Poppins\',Arial,sans-serif;font-size:22px;font-weight:700;color:#c60000;line-height:1.15;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}' +
        '.dps-print-header-subtitle{font-family:\'Poppins\',Arial,sans-serif;font-size:13px;font-weight:500;color:#212529;line-height:1.3;margin-top:3px;white-space:nowrap;}' +
        'table.dps-data-table{border-collapse:collapse!important;width:100%!important;table-layout:auto!important;margin-top:8px;}' +
        'table.dps-data-table thead th{font-family:\'Oswald\',\'Poppins\',Arial,sans-serif!important;text-align:center!important;vertical-align:middle!important;background-color:#c60000!important;color:#fff!important;border:.5pt solid #c60000!important;font-weight:600;padding:9px 6px!important;text-transform:uppercase;white-space:nowrap!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        'table.dps-data-table tbody td{border:.5pt solid #e3c9cb!important;padding:7px 8px!important;vertical-align:middle!important;white-space:normal!important;word-break:break-word!important;}' +
        'table.dps-data-table tbody tr:nth-child(even){background-color:#fff5f5!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        'table.dps-data-table tbody tr:nth-child(odd){background-color:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        '.dps-print-watermark{margin-top:14px;margin-right:8mm;text-align:right;font-family:\'Poppins\',Arial,sans-serif;font-size:9pt;color:#777;page-break-inside:avoid;display:flex;align-items:center;justify-content:flex-end;gap:6px;}' +
        '.dps-print-watermark img{width:24px;height:24px;object-fit:contain;display:inline-block;vertical-align:middle;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        '.dps-print-watermark span{vertical-align:middle;white-space:nowrap;}' +
        '</style>',
    );

    var $table = $win.find('body table').first();
    $table.addClass('dps-data-table');
    $win.find('body').find('h1').remove();

    var colCount = $table.find('thead th').length;
    var bodyFont = colCount > 12 ? '8px' : colCount > 9 ? '9px' : '10px';
    var cellPad = colCount > 12 ? '4px 5px' : '7px 8px';
    $table.find('th, td').css({ 'font-size': bodyFont, padding: cellPad });

    if (opts.watermark && opts.watermark.text) {
      var logo = opts.watermark.logoUrl
        ? '<img src="' + opts.watermark.logoUrl + '" alt="Digital Pencak Silat">'
        : '';
      $win.find('body').append('<div class="dps-print-watermark">' + logo + '<span>' + opts.watermark.text + '</span></div>');
    }
  };

  window.dpsReportExcelCustomize = function (xlsx, opts) {
    opts = opts || {};
    var forceUppercase = opts.uppercase === true;
    var sheet = xlsx.xl.worksheets['sheet1.xml'];
    var styles = xlsx.xl['styles.xml'];

    var addStyle = function (xml, styleStr) {
      var el = xml.getElementsByTagName('cellXfs')[0];
      var newStyle = new DOMParser().parseFromString(styleStr, 'text/xml').childNodes[0];
      el.appendChild(newStyle);
      return el.childNodes.length - 1;
    };

    var fonts = styles.getElementsByTagName('fonts')[0];
    $(fonts).append('<font><sz val="15"/><name val="Calibri"/><b/><color rgb="FFC60000"/></font>');
    var fontTitleIdx = fonts.childNodes.length - 1;
    $(fonts).append('<font><sz val="12"/><name val="Calibri"/><b/><color rgb="FFFFFFFF"/></font>');
    var fontHdrIdx = fonts.childNodes.length - 1;
    $(fonts).append('<font><sz val="11"/><name val="Calibri"/><color rgb="FF000000"/></font>');
    var fontBdyIdx = fonts.childNodes.length - 1;

    var fills = styles.getElementsByTagName('fills')[0];
    $(fills).append('<fill><patternFill patternType="solid"><fgColor rgb="FFC60000"/><bgColor indexed="64"/></patternFill></fill>');
    var fillRedIdx = fills.childNodes.length - 1;
    $(fills).append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFF5F5"/><bgColor indexed="64"/></patternFill></fill>');
    var fillZebraIdx = fills.childNodes.length - 1;

    var styleTitleIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontTitleIdx + '" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="0"/></xf>');
    var styleHeaderIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="' + fillRedIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="0"/></xf>');
    var styleBodyIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="0"/></xf>');
    var styleBodyZebraIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="' + fillZebraIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="0"/></xf>');

    $('row:eq(0) c', sheet).attr('s', styleTitleIdx);
    $('row:eq(1) c', sheet).attr('s', styleHeaderIdx);
    $('row:gt(1) c', sheet).attr('s', styleBodyIdx);
    $('row:gt(1)', sheet).each(function (rowIdx) {
      if (rowIdx % 2 === 1) {
        $('c', this).attr('s', styleBodyZebraIdx);
      }
    });

    if (forceUppercase) {
      $('row c', sheet).each(function () {
        $(this)
          .find('v, t')
          .each(function () {
            var text = $(this).text();
            if (isNaN(text)) $(this).text(text.toUpperCase());
          });
      });
    }
  };

  window.initAdminExportTable = function (selector, config) {
    config = config || {};
    if (!$(selector).length || !$.fn.DataTable) return null;

    normalizeEmptyBodyRows(selector);

    var preset = presets[config.preset] || presets['simple-list'];
    var title = config.title || document.title || 'Data Export';
    var filename = config.filename || title;
    var orientation = config.orientation || preset.orientation;
    var exportColumns = config.exportColumns || ':visible:not(' + (config.excludeColumns || '.no-export') + ')';
    var printHeader = Object.assign({ title: title, subtitle: '' }, config.printHeader || {});
    var buttons = [];

    if ($.fn.dataTable.Buttons) {
      buttons.push(
        Object.assign(
          {
            extend: 'colvis',
            className: 'btn btn-outline-secondary btn-sm',
            text: '<i class="fas fa-columns me-1"></i> Pilih Kolom',
          },
          config.colvis || {},
        ),
      );

      if ($.fn.dataTable.ext.buttons.excelHtml5) {
        var excelCfg = config.excel || {};
        var numTextCols = excelCfg.numericTextColumns || [];

        buttons.push(
          Object.assign(
            {
              extend: 'excelHtml5',
              title: title,
              filename: filename,
              className: 'btn btn-success btn-sm',
              text: '<i class="fas fa-file-excel me-1"></i> Excel',
              exportOptions: {
                columns: exportColumns,
                format: {
                  body: function (data, row, column, node) {
                    if (typeof config.exportFormatBody === 'function') {
                      data = config.exportFormatBody(data, row, column, node);
                    }
                    if (typeof data === 'string' && data.indexOf('<') !== -1) {
                      data = data
                        .replace(/<!--[\s\S]*?-->/g, '')
                        .replace(/<[^>]*>/g, '')
                        .replace(/&nbsp;/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                    }
                    if (numTextCols.indexOf(column) !== -1) {
                      var stripped = data ? String(data).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() : '';
                      if (stripped && /^\d{10,}$/.test(stripped)) return '\u200B' + stripped;
                      return stripped;
                    }
                    return data;
                  },
                },
              },
              customize: function (xlsx) {
                var columnWidths = excelCfg.columnWidths || {};
                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                var colElement = sheet.getElementsByTagName('cols')[0];

                if (!colElement && Object.keys(columnWidths).length) {
                  colElement = sheet.createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'cols');
                  sheet.insertBefore(colElement, sheet.getElementsByTagName('sheetData')[0]);
                }

                if (colElement && Object.keys(columnWidths).length) {
                  $(colElement).empty();
                  Object.keys(columnWidths).forEach(function (colName) {
                    var colIndex = colName.toUpperCase().charCodeAt(0) - 64;
                    $(colElement).append('<col min="' + colIndex + '" max="' + colIndex + '" width="' + columnWidths[colName] + '" customWidth="1"/>');
                  });
                }

                if (typeof excelCfg.customize === 'function') {
                  excelCfg.customize(xlsx);
                } else {
                  window.dpsReportExcelCustomize(xlsx, { uppercase: config.excelUppercase === true });
                }
              },
            },
            config.excelButton || {},
          ),
        );
      }

      if ($.fn.dataTable.ext.buttons.print) {
        buttons.push(
          Object.assign(
            {
              extend: 'print',
              title: title,
              filename: filename,
              orientation: orientation,
              className: 'btn btn-info btn-sm',
              text: '<i class="fas fa-print me-1"></i> Cetak',
              exportOptions: {
                columns: exportColumns,
                format:
                  typeof config.printFormatBody === 'function' || typeof config.exportFormatBody === 'function'
                    ? {
                        body: function (data, row, column, node) {
                          if (typeof config.printFormatBody === 'function') {
                            return config.printFormatBody(data, row, column, node);
                          }
                          return config.exportFormatBody(data, row, column, node);
                        },
                      }
                    : undefined,
              },
              customize: function (win) {
                var $body = $(win.document.body);
                var $head = $(win.document.head);
                var headerHtml =
                  config.printHeaderHtml ||
                  '<div class="dps-print-header">' +
                    '<div class="dps-print-header-title">' +
                    printHeader.title +
                    '</div>' +
                    (printHeader.subtitle ? '<div class="dps-print-header-subtitle">' + printHeader.subtitle + '</div>' : '') +
                    '</div>';

                $body.prepend(headerHtml);
                $body.find('h1').remove();
                $head.append(
                  '<style>' +
                    '@page{size:' +
                    orientation +
                    ';margin:.5cm;}' +
                    'body{font-family:Helvetica,Arial,sans-serif;font-size:' +
                    preset.fontSize +
                    ';}' +
                    'table{border-collapse:collapse!important;width:' +
                    preset.tableWidth +
                    '!important;margin-left:auto!important;margin-right:auto!important;}' +
                    'th{background:#f2f2f2!important;font-weight:700;text-align:center!important;}' +
                    'th,td{border:.3pt solid #555!important;padding:4px!important;vertical-align:middle!important;white-space:normal!important;word-break:break-word!important;}' +
                    'tbody tr:nth-child(even){background:#f9f9f9!important;}' +
                    '.text-center{text-align:center!important;}' +
                    '.text-end{text-align:right!important;}' +
                    '</style>',
                );

                if (typeof config.printCustomize === 'function') {
                  config.printCustomize(win);
                } else if (config.themedExport) {
                  window.dpsReportPrintCustomize(win, { watermark: config.watermark || null });
                }
              },
            },
            config.printButton || {},
          ),
        );
      }
    }

    var tableApi = initFallbackDataTable(
      selector,
      Object.assign(
        {
          dom:
            "<'admin-table-toolbar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3'<'d-flex flex-wrap align-items-center gap-2'Bl><'admin-search'f>>" +
            "<'table-responsive'tr>" +
            "<'d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3'<'small text-muted'i><'p-0'p>>",
          buttons: config.buttons || buttons,
        },
        config.dataTable || {},
      ),
    );

    syncExternalEmptyMessage(selector, tableApi);
    return tableApi;
  };
})();

