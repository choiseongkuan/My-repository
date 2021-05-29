<script src='js/jquery.min.js'></script>
<script src="js/qrcode.min.js"></script>
<script src="js/swipe-listener.min.js"></script>
<script src="js/index.umd.min.js"></script>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script>
let nowPage = 'recycle';
if (window.safari) {
    history.pushState(null, null, location.href);
    window.onpopstate = function(event) {
        history.go(1);
    };
}

window.addEventListener('popstate', function (event) {
  history.pushState(null, document.title, location.href);
});
    if(("standalone" in window.navigator) && window.navigator.standalone){
    
    	// If you want to prevent remote links in standalone web apps opening Mobile Safari, change 'remotes' to true
    	var noddy, remotes = false;
    	
    	document.addEventListener('click', function(event) {
    		
    		noddy = event.target;
    		
    		// Bubble up until we hit link or top HTML element. Warning: BODY element is not compulsory so better to stop on HTML
    		while(noddy.nodeName !== "A" && noddy.nodeName !== "HTML") {
    	        noddy = noddy.parentNode;
    	    }
    		
    		if('href' in noddy && noddy.href.indexOf('http') !== -1 && (noddy.href.indexOf(document.location.host) !== -1 || remotes))
    		{
    			event.preventDefault();
    			document.location.href = noddy.href;
    		}
    	
    	},false);
    }
    function switchActivePage (data) {
        $(".app").removeClass("active");
        $("footer>div").removeClass("active");
        $(".app#"+data).addClass("active");
        $("footer>div."+data).addClass("active");
        nowPage = data;
    }
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
        let fData = new FormData();
        document.querySelector("#profile-pic").addEventListener('change', (e) => {
            fData.append("profile_pic", e.target.files[0]);
        });
        $("#account-submit-btn").on("click", function(){
            fData.append("username", $("#username").val());
            fData.append("email", $("#email").val());
            fData.append("nickname", $("#nickname").val());
            fData.append("region", $("#region option:selected").val());
            startLoading();
            console.log(username, email, nickname, region);
            axios.post('check-account.php', fData)
            .then(function (res) {
                finishLoading();
                let error = res.data.error;
                console.log(error);
                if( error == true ){
                    clearPage("account");
                    alert(JSON.parse(res)["content"]);
                    console.log(res)
                } else {
                    location.reload();
                }
            })
            .catch(function (error) {
                finishLoading();
                console.log(error);
            });
        })
        $("#password-submit-btn").on("click", function(){
            var password = $("#new_password").val();
            var password_again = $("#new_password_again").val();
            var current_password = $("#current_password").val();
            startLoading();
            $.ajax({
                url: "check-changepassword.php",
                method: "post",
                data: {
                    password: password,
                    password_again: password_again,
                    current_password: current_password,
                },
                error: () => {
                    finishLoading();
                    // console.log(res);
                },
                success: (res) => {
                    finishLoading();
                    let error = JSON.parse(res)["error"];
                    if( error == true ){
                        clearPage("password");
                        console.log(JSON.parse(res)["usrn"])
                        alert(JSON.parse(res)["content"]);
                    } else {
                        clearPage("password");
                        closeSidePage("password");
                    }
                }
                
            })
        })
        $("#footprint-submit-btn").on("click", function(){
            startLoading();
            $.ajax({
                url: "check-footprint.php",
                method: "get",
                data: {
                    month: $("#month").val()
                },
                error: () => {
                    finishLoading();
                },
                success: (res) => {
                    finishLoading();
                    let html="";
                    let error = JSON.parse(res)["error"];
                    if( error == true ){
                        html = "<li><div class='info'>No result.</div></li>";
                        $("#footprint-total-value").html("0")
                    } else {
                        console.log(JSON.parse(res)["total"]);
                        JSON.parse(res)["content"].forEach(function(e) {
                            console.log(e);
                            html += (`
                                <li>
                                    <div class='info'>
                                        <div class='user-list-info-left'>
                                            <span class='title'>${e.description}</span>
                                            <span class='date'>${e.date}</span>
                                        </div>
                                        <span class='amount ${e.mark} ${e.amount}
                                        </span>
                                    </div>
                                </li>
                            `);
                        });
                        $("#footprint-total-value").html(JSON.parse(res)["total"])
                    }
                    $("#footprint-list").html(html);
                }
                
            })
        })
        
        $("#code-submit-btn").on("click", function(){
            startLoading();
            $.ajax({
                url: "check-code.php",
                method: "post",
                data: {
                    code: $("#code").val()
                },
                error: () => {
                    finishLoading();
                },
                success: (res) => {
                    finishLoading();
                    let html="";
                    let error = JSON.parse(res)["error"];
                    if( error == true ){
                        alert(JSON.parse(res)["content"]["code"]);
                        $("#code").val("");
                    } else {
                        alert("Thank you for recycling!");
                    }
                }
                
            })
        })
        
        
        
        const ptr = PullToRefresh.init({
            mainElement: "#ranking",
            onRefresh() {
                if ( nowPage == "ranking" ) {
                    startLoading();
                    axios.get('check-ranking.php')
                        .then(function (res) {
                            let html = "";
                            let error = res.data.error;
                            if( error == false ){
                                res.data.content.forEach(function(e) {
                                    html += `
                                        <li class="ranking-list-item ${e.focus}">
                                            <h3 class="number">${e.number}</h3>
                                            <img alt="The profile picture of ${e.username} " src="img/user/${e.photo_path}">
                                            <h3 class="username">${e.username}</h3>
                                            <h3 class="footprint">${e.footprint}</h3>
                                        </li>
                                    `;
                                });
                                if ( res.data.user_on_table == false){
                                    html += `
                                        <li class="ranking-list-item focus">
                                            <h3 class="number"></h3>
                                            <img alt="The profile picture of ${res.data.this_user.username} " src="img/user/${res.data.this_user.photo_path}">
                                            <h3 class="username">${res.data.this_user.username}</h3>
                                            <h3 class="footprint">${res.data.this_user.footprint}</h3>
                                        </li>
                                    `;
                                }
                                $(".ranking-list").html(html);
                            }
                            finishLoading();
                        })
                        .catch(function (error) {
                            finishLoading();
                            console.log(error);
                        });
                }
            }
        });
    })
    
</script>

<?php
    echo "<script>";
    echo "$(document).ready(function(){" . "\n";
        
        $allElements = ["account", "password", "about", "tax", "footprint"];
        for ( $i = 0 ; $i < count($allElements) ; $i++ ){
            echo "var container_".$i." = document.querySelector('#".$allElements[$i]."');". "\n";
            echo "var listener_".$i." = SwipeListener(container_".$i.");". "\n";
            echo "container_".$i.".addEventListener('swipe', function (e) {". "\n";
            echo "    var directions".$i." = e.detail.directions;". "\n";
            echo "    if (directions".$i.".right && e.detail.x[0] < 50) {". "\n";
            echo "        closeSidePage('".$allElements[$i]."');". "\n";
            echo "        clearPage('".$allElements[$i]."');". "\n";
            echo "    }". "\n";
            echo "})". "\n";
        }
        
        echo "let accountDefaultData = { username:'";
        echo $user_info["username"];
        echo "', email:'";
        echo $user_info["email"];
        echo "', nickname:'";
        echo $user_info["nickname"];
        echo "', region: '";
        echo $user_region_code;
        echo "'};\n";
        
    echo "})";
    echo "</script>";
?>
