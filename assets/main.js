"use strict";
/**
 * returns a static (not live) NodeList representing a list of the document's elements that match the specified group of selectors. 
 * @param {string} selector 
 * @param {Element} [document] base 
 * @returns {(NodeList|Element|null)}
 */
const $ = (selector, base = document) => {
  let elements = base.querySelectorAll(selector);
  return (elements.length == 1) ? elements[0] : elements;
}

document.addEventListener("DOMContentLoaded", ev => {
  getSettings();

  // initial qr
  doQR(ev);

  // set the handler for the ok button
  $("#do-qr").onclick = doQR;
  $("#container").onclick = downloadSVG;
  $("#bg-color").onchange = doQR;
  $("#color").onchange = doQR;
  $("#size").onchange = doQR;

  onRowClick("code-table", row => {
    $("#url").value = $("#base-url").value + $("td", row)[0].textContent;
    let cell = $("td", row)[1];
    cell.contentEditable = true;
    cell.classList.add("edit");

    cell.addEventListener("blur", () => {
      cell.contentEditable = false;
      cell.classList.remove("edit");
    })
    cell.addEventListener("keydown", ev => {
      ev.stopImmediatePropagation();
      if (ev.keyCode == 13) {
        cell.contentEditable = false;
        cell.classList.remove("edit");
        console.log(`save data ${cell.textContent}`);
      }
      if (ev.keyCode == 27) {
        cell.contentEditable = false;
        cell.classList.remove("edit");
      }
    }, { passive: true, capture: false });
    cell.focus();
  });


  // select all on focus
  const url = $('#url');
  url.addEventListener('focus', ev => ev.target.select());

  // clear query_string
  history.pushState({}, '', '/');
  url.select();
  url.focus();

});

/**
 * shorten a url by creating a server request.
 */
const doShorten = ev => {

  const url = $('#url').value;
  window.location.assign = `<?=base_url?>/?url=${url}`;
  window.location.reload();

};

/**
 * create a qr code of the contents of url
 */
const doQR = ev => {
  const url = $('#url').value;

  if (url.length === 0) return; // no url, stop

  let qrcode = new QRCode({
    content: url,
    padding: 2,
    width: $('#size').value, height: $('#size').value,
    join: false,
    color: $('#color').value,
    background: $('#bg-color').value,
    ecl: "L"
  });

  $("#container").innerHTML = qrcode.svg();
  saveSettings();
  ev.preventDefault();
};

/**
 * create a download link
 */
const downloadSVG = () => {
  const svg = $('#container').children[0].outerHTML;
  const blob = new Blob([svg.toString()]);
  const element = document.createElement("a");
  try {
    const encode = new URL('', $('#shorten').dataset.fullUrl);
    element.download = encode.hostname.replaceAll('.', '_') + ".svg";
  } catch {
    // probably don't have a url
    element.download = 'qr.svg';
  }

  element.href = window.URL.createObjectURL(blob);
  element.click();
  element.remove();
};

/**
 * Read the settings from localStorage
 */
const getSettings = _ => {
  if (window.localStorage.getItem('settings')) {
    const settings = JSON.parse(window.localStorage.getItem('settings'));
    $('#bg-color').value = settings.bgColor;
    $('#color').value = settings.color;
    $('#size').value = settings.size;
  } else {
    $('#bg-color').value = '#ffffff';
    $('#color').value = '#000000';
    $('#size').value = '50';
  }
};
/**
 * Save the settings to localStorage
 */
const saveSettings = _ => {
  const settings = {
    bgColor: $('#bg-color').value,
    color: $('#color').value,
    size: $('#size').value
  }
  window.localStorage.setItem('settings', JSON.stringify(settings));
};

function onRowClick(tableId, callback) {
  var table = document.getElementById(tableId),
    rows = table.getElementsByTagName("tr"),
    i;
  for (i = 0; i < rows.length; i++) {
    table.rows[i].onclick = function (row) {
      return function () {
        callback(row);
      };
    }(table.rows[i]);
  }
};
