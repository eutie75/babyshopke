// Baby Shop KE frontend helpers
document.addEventListener("DOMContentLoaded", function () {
    var anchors = document.querySelectorAll('a[href^="#"]');
    anchors.forEach(function (anchor) {
        anchor.addEventListener("click", function (event) {
            var href = anchor.getAttribute("href");
            if (!href || href.length < 2) {
                return;
            }

            var target = document.querySelector(href);
            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    });

    setTimeout(function () {
        document.querySelectorAll(".flash").forEach(function (flash) {
            flash.style.transition = "opacity 0.5s ease";
            flash.style.opacity = "0";
            setTimeout(function () {
                if (flash.parentNode) {
                    flash.parentNode.removeChild(flash);
                }
            }, 520);
        });
    }, 4500);
});
