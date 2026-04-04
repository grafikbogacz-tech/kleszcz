function WHCreateCookie(name, value, days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toUTCString();
    document.cookie = name + "=" + value + expires + "; path=/";
}

function WHReadCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

window.addEventListener('load', WHCheckCookies);

function WHCheckCookies() {
    if (WHReadCookie('cookies_accepted') !== 'T') {
        var container = document.createElement('div');
        container.id = 'cookies-message-container';
        container.innerHTML =
            '<div id="cookies-message" style="' +
                'position:fixed; bottom:0; left:0; right:0;' +
                'background:#fff;' +
                'box-shadow:0 -4px 20px rgba(0,0,0,0.15);' +
                'z-index:99999;' +
                'font-family:DM Sans,sans-serif;' +
                'font-size:14px; line-height:1.6; color:#333;' +
                'padding:24px 20px 20px; text-align:center;' +
            '">' +
                '<div style="max-width:860px; margin:0 auto;">' +
                    '<p style="font-weight:700; font-size:15px; color:#111; margin-bottom:8px;">' +
                        'Twoje dane należą do Ciebie i wspieramy Twoje prawo do prywatności.' +
                    '</p>' +
                    '<p style="margin-bottom:18px; color:#555;">' +
                        'Aby zapewnić najlepszą jakość korzystania z naszej witryny, używamy plików cookie lub podobnych technologii. ' +
                        'Możesz zaakceptować poziom <strong>Zrównoważony</strong>, który pozwala na standardowe działanie strony i anonimową analizę ruchu, bez śledzenia reklamowego.' +
                    '</p>' +

                    '<div style="display:inline-block; background:#f0f0f0; border-radius:30px; padding:4px; margin-bottom:14px;">' +
                        '<span style="display:inline-block; background:#fff; border-radius:30px; padding:7px 22px; font-weight:600; font-size:14px; color:#333; box-shadow:0 1px 4px rgba(0,0,0,0.12);">' +
                            '⚖️&nbsp;Zrównoważony' +
                        '</span>' +
                    '</div>' +

                    '<p style="font-size:13px; color:#666; margin-bottom:20px;">' +
                        'Standardowy poziom prywatności. Pliki cookie służą do poprawnego działania strony oraz anonimowej analizy ruchu. Dane nie są udostępniane w celach reklamowych.' +
                    '</p>' +

                    '<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">' +
                        '<a href="polityka.html" target="_blank" style="color:#005117; font-size:13px; text-decoration:underline; cursor:pointer;">Polityka prywatności</a>' +

                        '<button onclick="WHCloseCookiesWindow();" ' +
                            'onmouseover="this.style.background=\'#27ae60\'" ' +
                            'onmouseout="this.style.background=\'#2ecc71\'" ' +
                            'style="background:#2ecc71; color:#fff; border:none; border-radius:30px; padding:12px 34px; font-size:15px; font-weight:600; cursor:pointer; font-family:DM Sans,sans-serif; letter-spacing:0.02em;">' +
                            'Zapisz moje preferencje' +
                        '</button>' +

                        '<a href="polityka.html" target="_blank" style="color:#005117; font-size:13px; text-decoration:underline; cursor:pointer;">Nie sprzedawaj danych</a>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(container);
    }
}

function WHCloseCookiesWindow() {
    WHCreateCookie('cookies_accepted', 'T', 365);
    var container = document.getElementById('cookies-message-container');
    if (container) container.parentNode.removeChild(container);
}
