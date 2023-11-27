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
const api = '/api/index.php';

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
  $("#code-table").addEventListener('click', ev => {
    // the parent of a cell is probably a rwo. should test this
    doRowClick(ev.target.parentElement);
  },{ passive: true, capture: false } );
  // prevent enter in url
  $('#url').addEventListener('keypress', ev=> {
    if(ev.key=="Enter")
      ev.preventDefault()
    }
  );
  // $('#logoff').addEventListener("click", _=> {
  //   window.location.replace( '/logon/' );
  // }, { capture: true });

/**
 * Eventhandler for row clicks
 */
const doRowClick = row => {
  // ignore none td clicks
  if (row && $("td", row).length === 0) return false;

  $("#url").value = $("#base-url").value + $("td", row)[0].textContent;
  $('#shorten').dataset.fullUrl = $("td", row)[1].textContent
  document.getElementById("do-qr").click()

  let cellCode = $("td", row)[0];
  let cellUrl = $("td", row)[1];
  let cellUrlValue = cellUrl.textContent;

/**
 * 
 * @param {Element} el 
 */
  const startEdit = (el) => {
    cellUrl.contentEditable = true;
    cellUrl.focus();
  }
  /**
   * 
   * @param {Element} el 
   * @param {string} newtext somehow this is needed for Chrome
   */
  const endEdit = (el, newtext) => {
    cellUrl.removeEventListener("blur", blurEH);
    cellUrl.removeEventListener("keydown", keyEH);

    cellUrl.contentEditable = false;
    cellUrl.textContent = newtext;
  }

  startEdit(cellUrl);
  /**
   * on loosing focus, abort edit
   */
  const blurEH = _ => {
    endEdit(cellUrl, cellUrlValue);
  };
  cellUrl.addEventListener("blur", blurEH), { passive: true };

  /**
   * handle Enter and Esc events
   */
  const keyEH = ev => {
    switch (ev.key) {
      default: break;
      case "Enter":
        console.log(`save data ${cellUrl.textContent}`);
        doSetUrl(cellCode.textContent, cellUrl.textContent);
        $("#url").value = $("#base-url").value + $("td", row)[0].textContent;
        endEdit(cellUrl, cellUrl.textContent);
        break;
      case "Escape":
        endEdit(cellUrl, cellUrlValue);
        break;
    }
  };
  cellUrl.addEventListener("keydown", keyEH, { passive: true });
  // doesn't work: const onFocusEH = cellUrl.addEventListener('focus', ev=> ev.target.select());
};

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

async function doSetUrl(code, url) {
  let body = { url: url };
  let options = { method: 'PUT', body: JSON.stringify(body) }

  const response = await fetch( `${api}/Code/${code}`, options );
  if (!response.ok) {
    console.error(response);
  } else {
    const result = await response.json();
    console.log(result);
  }

}