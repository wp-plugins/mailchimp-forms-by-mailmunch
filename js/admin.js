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

function repositionSignupBox() {
    divId = 'signup-signin-box';
    var divWidth, divHeight;
    var objDiv = document.getElementById(divId);

    if (objDiv.clientWidth) {
        divWidth = objDiv.clientWidth;
        divHeight = objDiv.clientHeight;
    }
    else if (objDiv.offsetWidth)
    {
        divWidth = objDiv.offsetWidth;
        divHeight = objDiv.offsetHeight;
    }

    // Get the x and y coordinates of the center in output browser's window 
    var centerX, centerY;
    if (window.innerHeight)
    {
        centerX = window.innerWidth;
        centerY = window.innerHeight;
    }
    else if (document.documentElement && document.documentElement.clientHeight)
    {
        centerX = document.documentElement.clientWidth;
        centerY = document.documentElement.clientHeight;
    }
    else if (document.body)
    {
        centerX = document.body.clientWidth;
        centerY = document.body.clientHeight;
    }

    var offsetLeft = (centerX - divWidth) / 2;
    var offsetTop = (centerY - divHeight) / 2;

    objDiv.style.top = offsetTop + 'px';
    objDiv.style.left = offsetLeft + 'px';
}

function showSignInForm() {
    document.getElementById("sign-up-form").style.display = 'none';
    document.getElementById("sign-in-form").style.display = 'block';
    document.getElementById('why-account').style.display = 'none';
    showSignupBox();
}

function showSignUpForm() {
    document.getElementById("sign-in-form").style.display = 'none';
    document.getElementById("sign-up-form").style.display = 'block';
    document.getElementById('why-account').style.display = 'none';
    showSignupBox();
}

function showSignupBox(width, height) {
    document.getElementById("signup-signin-box-overlay").style.display = 'block';
    document.getElementById("signup-signin-box").style.display = 'block';
    repositionSignupBox();

    return false;
}

function hideSignupBox() {
    document.getElementById("signup-signin-box-overlay").style.display = 'none';
    document.getElementById("signup-signin-box").style.display = 'none';
}

function showWhyAccount() {
    document.getElementById('why-account').style.display = 'block';
    repositionSignupBox();
}
