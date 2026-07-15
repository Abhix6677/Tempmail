document.addEventListener("livewire:init", () => {
    Livewire.on("showAlert", (params) => {
        if (params[0]) {
            const type = params[0].type;
            const message = params[0].message;
            if (type && message) {
                alertify(message, type);
            }
        }
    });

    Livewire.on("showNewMailNotification", (notification) => {
        // notification is passed as array of named params
        const data = notification[0] || notification;
        if (data && data.subject) {
            notifyUser(data.subject, `${data.sender_name || ''} - ${data.sender_email || ''}`);
        }
    });
});

document.addEventListener("showAlert", (params) => {
    if (params && params.detail && params.detail.message && params.detail.type) {
        alertify(params.detail.message, params.detail.type);
    }
});

function alertify(message = "", type = "success") {
    const icons = {
        error: `<div class="text-red-500 w-10"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>`,
        success: `<div class="text-green-500 w-10"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>`,
        warning: `<div class="text-yellow-500 w-10"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>`,
    };
    const shades = {
        success: "bg-green-50 border-green-500",
        error: "bg-red-50 border-red-500",
        warning: "bg-yellow-50 border-yellow-500",
    };
    const el = document.createElement("div");
    const label = document.querySelector(`.language-helper .${type}`)?.innerText || type;
    el.innerHTML = `
        <div x-data="{ open: false }" x-init="() => { setTimeout(() => { open = true }, 100); setTimeout(() => { open = false }, 3000) }" class="fixed flex items-center justify-center top-0 right-0 w-full z-50">
            <div class="w-full max-w-sm m-2 py-3 px-4 overflow-hidden shadow-lg rounded-md flex items-center border ${shades[type]}"
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-5"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-5"
            >
                ${icons[type]}
                <div class="ml-4 flex-1">
                    <div class="text-lg text-gray-600 font-semibold">${label}</div>
                    <div class="text-sm text-gray-600">${message}</div>
                </div>
                <div x-on:click="open = false" class="ml-4 text-gray-500 w-5 cursor-pointer hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>
    `;
    setTimeout(() => document.body.append(el), 100);
}

function notifyUser(title = "", body = "") {
    const options = {
        body,
        icon: "/images/favicon.png",
    };
    if (!title) return;
    const showNotification = () => {
        const n = new Notification(title, options);
        n.onclick = () => window.focus();
    };
    if (Notification.permission === "granted") {
        showNotification();
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then((permission) => {
            if (permission === "granted") {
                showNotification();
            }
        });
    }
}
