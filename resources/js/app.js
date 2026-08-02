import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('[data-mobile-menu]');
  document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => menu?.classList.toggle('hidden'));
  const modal = document.querySelector('[data-modal]');
  document.querySelectorAll('[data-open-modal]').forEach(button => button.addEventListener('click', () => modal?.classList.remove('hidden')));
  document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => modal?.classList.add('hidden')));
  document.querySelectorAll('[data-toast]').forEach(button => button.addEventListener('click', () => {
    const toast = document.querySelector('[data-toast-message]');
    if (!toast) return;
    toast.classList.remove('translate-y-24','opacity-0');
    setTimeout(() => toast.classList.add('translate-y-24','opacity-0'), 2700);
  }));
  document.querySelectorAll('[data-toggle]').forEach(button => button.addEventListener('click', () => {
    button.classList.toggle('bg-violet'); button.classList.toggle('bg-white/15');
    button.querySelector('span')?.classList.toggle('translate-x-4');
  }));
  document.querySelectorAll('[data-tab]').forEach(button => button.addEventListener('click', () => {
    const group = button.dataset.tabGroup;
    document.querySelectorAll(`[data-tab-group="${group}"]`).forEach(el => el.classList.remove('bg-violet/15','text-white'));
    button.classList.add('bg-violet/15','text-white');
    document.querySelectorAll(`[data-panel-group="${group}"]`).forEach(el => el.classList.toggle('hidden', el.dataset.panel !== button.dataset.tab));
  }));
  document.querySelectorAll('[data-upload-form]').forEach(form => form.addEventListener('submit', () => {
    const progress = form.querySelector('[data-upload-progress]');
    const bar = form.querySelector('[data-upload-bar]');
    const percent = form.querySelector('[data-upload-percent]');
    const label = form.querySelector('[data-upload-label]');
    progress?.classList.remove('hidden');
    if (label) label.textContent = 'Uploading and parsing';
    if (bar) bar.style.width = '72%';
    if (percent) percent.textContent = '72%';
    form.querySelector('button')?.setAttribute('disabled', 'disabled');
  }));
  document.querySelectorAll('[data-ai-form]').forEach(form => form.addEventListener('submit', () => {
    const button = form.querySelector('button');
    if (!button) return;
    button.textContent = 'Analyzing...';
    button.setAttribute('disabled', 'disabled');
  }));
});
