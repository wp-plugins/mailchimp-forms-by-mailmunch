jQuery.unparam = function (value) {
    var
    // Object that holds names => values.
    params = {},
    // Get query string pieces (separated by &)
    pieces = value.split('&'),
    // Temporary variables used in loop.
    pair, i, l;

    // Loop through query string pieces and assign params.
    for (i = 0, l = pieces.length; i < l; i++) {
        pair = pieces[i].split('=', 2);
        // Repeated parameters with the same name are overwritten. Parameters
        // with no value get set to boolean true.
        params[decodeURIComponent(pair[0])] = (pair.length == 2 ?
            decodeURIComponent(pair[1].replace(/\+/g, ' ')) : true);
    }

    return params;
};

window.onmessage = function (e) {
  if (e.data === 'refresh') {
    top.location.reload();
  }
};

function showSignInForm() {
    document.getElementById("sign-up-form").style.display = 'none';
    document.getElementById("sign-in-form").style.display = 'block';
}

function showSignUpForm() {
    document.getElementById("sign-in-form").style.display = 'none';
    document.getElementById("sign-up-form").style.display = 'block';
}

function showSignupBox() {
    document.getElementById('why-account').style.display = 'none';
    tb_show("Create Account","#TB_inline?width=550&height=450&inlineId=signup-signin-box",null);
    return false;
}

function showWhyAccount() {
    document.getElementById('why-account').style.display = 'block';
    tb_show("Create Account","#TB_inline?width=550&height=500&inlineId=signup-signin-box",null);
}
