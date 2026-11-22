const avisSelect = document.getElementById('avis');
const avisTitle = document.getElementById('avisTitle');
const avisDetails = document.getElementById('avisDetails');

function updateAvisPreview() {
  const selected = avisSelect.selectedOptions[0];
  if (!selected || !selected.value) {
    avisTitle.textContent = 'Choisissez une publication à signaler';
    avisDetails.textContent = 'Le titre de l’avis apparaîtra ici.';
    return;
  }

  avisTitle.textContent = selected.textContent;
  avisDetails.textContent = selected.dataset.details || '';
}

const requestedAvis = new URLSearchParams(window.location.search).get('avis');
if (requestedAvis && Array.from(avisSelect.options).some((option) => option.value === requestedAvis)) {
  avisSelect.value = requestedAvis;
}

avisSelect.addEventListener('change', updateAvisPreview);
updateAvisPreview();
