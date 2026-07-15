document.addEventListener("click", (e) => {
    if (e.target.id == "delivery-copy-btn") {
        navigator.clipboard.writeText(e.target.dataset.key);
        e.target.innerText = e.target.dataset.copied;
        setTimeout(() => {
            e.target.innerText = e.target.dataset.copy;
        }, 3000);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const button = document.getElementById("view-website-btn");
    if (button) {
        const showButton = () => {
            button.classList.remove("opacity-0", "translate-y-10", "scale-95", "pointer-events-none");
            button.classList.add("opacity-100", "translate-y-0", "scale-100", "pointer-events-auto");
        };
        const hideButton = () => {
            button.classList.remove("opacity-100", "translate-y-0", "scale-100", "pointer-events-auto");
            button.classList.add("opacity-0", "translate-y-10", "scale-95", "pointer-events-none");
        };
        window.addEventListener("mousemove", (e) => {
            const threshold = window.innerHeight * 0.8; // bottom 20%
            if (e.clientY >= threshold) {
                showButton();
            } else {
                hideButton();
            }
        });
    }
});
