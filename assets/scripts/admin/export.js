const { __ } = wp.i18n

export default function() {

  let exportToggle = document.querySelector('.formality-export-toggle')
  let exportPanel = document.querySelector('.export-panel')
  let exportForm
  let exportLink
  let exportMessage
  let exportProgressbar
  let exportCheck
  let exportTimer
  let exportCleanup
  let exportTime = 0;
  let exportTotal = 0;

  if(typeof(exportPanel) != 'undefined' && exportPanel != null){

    exportForm = exportPanel.querySelector('form')
    exportLink = exportForm.querySelector('.export-result > a')
    exportMessage = exportForm.querySelector('.export-result > p')
    exportProgressbar = exportForm.querySelector('.progress > .bar')
    exportCleanup = exportForm.querySelector('.export-cleanup > a')

    exportToggle.addEventListener('click', function(e){
      e.preventDefault();
      exportPanel.classList.toggle('hidden');
    })

    exportForm.addEventListener('submit', function(e){
      e.preventDefault();
      exportSubmit()
    })

    exportForm.addEventListener("input", function () {
      exportStats()
    });

    exportCleanup.addEventListener('click', function(e){
      e.preventDefault();
      const cleanupurl = exportCleanup.getAttribute('href')
      fetch(cleanupurl, { method: 'get' })
      .then((response) => {
        exportLink.setAttribute('href', '')
        exportLink.setAttribute('download', '')
        exportLink.innerText = '';
      })
    })
  }

  function exportStats(count=true, progress=0) {
    if(count) {
      const limit = parseInt(document.querySelector('input[name="export_limit"]').value)
      const offset = parseInt(document.querySelector('input[name="export_skip"]').value)
      const month = parseInt(document.querySelector('select[name="export_month"]').selectedOptions[0].getAttribute('data-results'))
      exportTotal = Math.max(Math.min(limit, month - offset), 0);
    }
    let time = progress ? parseInt((exportTime * exportTotal) / progress) - exportTime : Math.max(1, parseInt(exportTotal / 10))
    document.querySelector('.export-total-live').innerText = exportTotal;
    document.querySelector('.export-count-progress').innerText = progress;
    document.querySelector('.export-time-remaining').innerText = time;
  }

  function exportResume(url, error) {
    if(exportTime > 10) {
      if(!url.searchParams.get('resume')) {
        url.searchParams.append('resume', 1)
      }
      exportRequest(url)
    } else {
      exportError(error)
    }
  }

  function exportProgress(url) {
    let progressurl = new URL(url);
    progressurl.searchParams.append('progress', 1)
    fetch(progressurl, { method: 'get' })
    .then((response) => { if (response.ok) { return response.json(); } else { throw ''; } })
    .then(data => {
      if('progress' in data) {
        const progress = parseInt(data.progress);
        const percent = Math.min(parseInt(progress * 100 / exportTotal), 100)
        exportProgressbar.style.width = percent + '%';
        exportStats(false, progress)
      }
    })
    .catch((error) => {});
    exportCheck = setTimeout(function() { exportProgress(url) }, 5000)
  }

  function exportSubmit() {
    if(!exportForm.classList.contains('loading')) {
      exportLink.innerText = '';
      exportProgressbar.style.width = '0%';
      exportForm.classList.add('loading');
      exportStats()
      let url = new URL(exportForm.getAttribute('action'));
      for (const pair of new FormData(exportForm)) { url.searchParams.append(pair[0], pair[1]); }
      exportCheck = setTimeout(function() { exportProgress(url) }, 5000)
      exportTimer = setInterval(function(){ exportTime++ }, 1000)
      exportRequest(url)
    }
  }

  function exportRequest(url) {
    fetch(url, { method: 'get' })
    .then((response) => {
      if (response.ok) {
        return response.json();
      } else {
        throw '';
      }
    })
    .then(data => {
      if('url' in data) {
        exportSuccess(data)
      } else {
        exportError('error' in data ? data.error : '')
      }
    })
    .catch((error) => {
      exportResume(url, error)
    });
  }

  function exportSuccess(data) {
    exportProgressbar.style.width = '100%';
    document.querySelector('.export-count-progress').innerText = exportTotal;
    clearTimeout(exportCheck)
    clearInterval(exportTimer)
    exportTime = 0;
    setTimeout(function(){
      exportForm.classList.remove('loading');
      const filename = document.querySelector('input[name="export_filename"]').value
      exportLink.setAttribute('href', data.url)
      exportLink.setAttribute('download', filename ? filename + '.csv' : data.file)
      exportLink.innerText = __('Download now', 'formality');
      let click = document.createEvent('MouseEvents')
      click.initEvent('click' ,true ,true)
      exportLink.dispatchEvent(click)
    }, 800)
  }

  function exportError(message) {
    message = message || __('Something went wrong', 'formality');
    exportProgressbar.style.width = '0%';
    exportTime = 0;
    clearTimeout(exportCheck)
    clearInterval(exportTimer)
    setTimeout(function(){
      exportForm.classList.remove('loading');
      exportMessage.innerText = message
    }, 800)
  }
}
