<script src='js/jquery.min.js'></script>
<script src="js/swipe-listener.min.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script>
if (window.safari) {
    history.pushState(null, null, location.href);
    window.onpopstate = function(event) {
        history.go(1);
    };
}

window.addEventListener('popstate', function (event) {
  history.pushState(null, document.title, location.href);
});
    function startLoading () {
        $("#floting-snipper").addClass("active");
    }
    function finishLoading () {
        $("#floting-snipper").removeClass("active");
    }
    function openSidePage (data) {
        $("#"+data).addClass("showing");
    }
    function closeSidePage (data) {
        $("#"+data).removeClass("showing");
    }
    function clearPage (data) {
        if ( data == "password" ) { $("#password input").val(""); }
    }
    $(document).ready(function(){
        let fData_l = new FormData();
        $("#login-submit-btn").on("click", function(){
            fData_l.append("username", $("#login-username").val());
            fData_l.append("password", $("#login-password").val());
            startLoading();
            axios.post('/recygo/check-login.php', fData_l)
            .then(function (res) {
                finishLoading();
                let error = res.data.error;
                let errorContent = "";
                if( error == true ){
                    for( value in res.data.content ) {
                        errorContent += res.data.content[value] + "\n";
                    }
                    alert( errorContent );
                } else {
                    location.reload();
                }
            })
            .catch(function (error) {
                finishLoading();
                console.log(error);
            });
        })
        
        let fData_r = new FormData();
        document.querySelector("#profile-pic").addEventListener('change', (e) => {
            fData_r.append("profile_pic", e.target.files[0]);
        });
        $("#register-submit-btn").on("click", function(){
            fData_r.append("username", $("#username").val());
            fData_r.append("email", $("#email").val());
            fData_r.append("nickname", $("#nickname").val());
            fData_r.append("region", $("#region option:selected").val());
            fData_r.append("password", $("#password").val());
            fData_r.append("password_again", $("#password_again").val());
            startLoading();
            axios.post('/recygo/check-register.php', fData_r)
            .then(function (res) {
                finishLoading();
                console.log(res)
                let error = res.data.error;
                let errorContent = "";
                if( error == true ){
                    for( value in res.data.content ) {
                        errorContent += res.data.content[value] + "\n";
                    }
                    alert( errorContent );
                } else {
                    // location.reload();
                }
            })
            .catch(function (error) {
                finishLoading();
                console.log(error);
            });
        })
        
        var container = document.querySelector('#register');
        var listener = SwipeListener(container);
        container.addEventListener('swipe', function (e) {
            var directions0 = e.detail.directions;
            if (directions0.right && e.detail.x[0] < 50) {
                closeSidePage('register');
            }
        })
    })
</script>

