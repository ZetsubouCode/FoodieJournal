/* Runs before first paint. Never stores journal data or writes to the server. */
try {
    document.documentElement.dataset.vendingSession = sessionStorage.getItem('foodie.vending.power') === 'on' ? 'on' : 'off';
    document.documentElement.dataset.vendingDiscovered = sessionStorage.getItem('foodie.vending.discovered') === 'true' ? 'true' : 'false';
}
catch { document.documentElement.dataset.vendingSession = 'off'; }
