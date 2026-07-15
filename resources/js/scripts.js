(function () {
    // Captcha resize observer
    const captchaEl = document.querySelector(".captcha");
    if (captchaEl) {
        const observer = new ResizeObserver(() => resizeCaptcha());
        observer.observe(captchaEl);
    }

    const resizeCaptcha = () => {
        const captcha = document.querySelector(".captcha");
        if (!captcha) return;
        const userEl = document.getElementById("user");
        if (!userEl) return;
        let y = 0;
        if (document.querySelector(".h-captcha > iframe")) {
            y = document.querySelector(".h-captcha > iframe").offsetWidth;
        } else if (document.querySelector(".g-recaptcha > div")) {
            y = document.querySelector(".g-recaptcha > div").offsetWidth;
        }
        const x = userEl.offsetWidth;
        if (y > 0) {
            captcha.style.transform = `scale(${x / y}, 1)`;
            captcha.style.transformOrigin = "left";
        }
    };

    window.addEventListener("resize", resizeCaptcha);
    window.addEventListener("load", resizeCaptcha);

    // Copy Email ID
    document.querySelectorAll(".btn_copy").forEach((el) => {
        el.addEventListener("click", () => copyEmailAddress(el));
    });

    const copyEmailAddress = (element) => {
        const email = document.getElementById("email_id");
        if (!email) return;
        const el = document.createElement("input");
        el.type = "text";
        el.value = email.innerText;
        document.body.appendChild(el);
        const isiOSDevice = /ipad|iphone/i.test(navigator.userAgent);
        if (isiOSDevice) {
            const editable = el.contentEditable;
            const readOnly = el.readOnly;
            el.contentEditable = true;
            el.readOnly = false;
            const range = document.createRange();
            range.selectNodeContents(el);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            el.setSelectionRange(0, 999999);
            el.contentEditable = editable;
            el.readOnly = readOnly;
        } else {
            el.select();
        }
        document.execCommand("copy");
        el.remove();
        const message = document.querySelector(".language-helper .copy_text")?.innerText || "Copied!";
        const event = new CustomEvent("showAlert", {
            bubbles: true,
            detail: {
                type: "success",
                message,
            },
        });
        element.dispatchEvent(event);
    };

    // Remove selection on email_id click
    const emailIdEl = document.getElementById("email_id");
    if (emailIdEl) {
        emailIdEl.addEventListener("click", () => {
            emailIdEl.disabled = true;
        });
    }

    // Scroll to message-content on .messages click
    const messagesEl = document.querySelector(".messages");
    if (messagesEl) {
        messagesEl.addEventListener("click", () => {
            setTimeout(() => {
                const contentEl = document.querySelector(".message-content");
                if (contentEl) {
                    scroll({
                        top: contentEl.offsetTop,
                        behavior: "smooth",
                    });
                }
            }, 100);
        });
    }

    // Cookie Policy
    const cookieEl = document.getElementById("cookie");
    if (cookieEl) {
        document.addEventListener("DOMContentLoaded", () => {
            if (!localStorage.getItem("cookie")) {
                cookieEl.classList.remove("hidden");
                cookieEl.classList.add("flex");
            }
        });
    }

    // Cookie Policy Close
    const cookieCloseEl = document.getElementById("cookie_close");
    if (cookieCloseEl) {
        cookieCloseEl.addEventListener("click", () => {
            localStorage.setItem("cookie", "closed");
            cookieEl.classList.add("hidden");
            cookieEl.classList.remove("flex");
        });
    }

    // Download Email
    document.addEventListener("livewire:init", () => {
        Livewire.on("loadDownload", () => {
            document.querySelectorAll(".download").forEach((el) => {
                el.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (!document.querySelector(`#email-${e.target.dataset.id}`)) {
                        const a = document.createElement("a");
                        a.id = `email-${e.target.dataset.id}`;
                        a.download = `email-${e.target.dataset.id}.eml`;
                        a.href = makeTextFile(e.target.dataset.id);
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => a.remove(), 2000);
                    }
                });
            });
        });
    });

    const makeTextFile = (id) => {
        let textFile = null;
        const text = document.querySelector(`#message-${id} textarea`)?.value || "";
        const data = new Blob([text], { type: "text/plain" });
        if (textFile !== null) {
            window.URL.revokeObjectURL(textFile);
        }
        textFile = window.URL.createObjectURL(data);
        return textFile;
    };

    const extractBlogData = (isWp, item) => {
        if (isWp) {
            const link = item.link;
            const title = item.title.rendered.replace(/(<([^>]+)>)/gi, "");
            const excerpt = item.excerpt.rendered.replace(/(<([^>]+)>)/gi, "");
            const image = item._embedded["wp:featuredmedia"] ? item._embedded["wp:featuredmedia"][0].media_details.sizes.medium.source_url : "";
            const category = item._embedded["wp:term"][0][0].name;
            return {
                link,
                target: "_blank",
                title,
                excerpt,
                image,
                category,
                categories: [],
            };
        }
        let link = `/blog/${item.slug}`;
        if (document.URL.includes(document.documentElement.lang)) {
            link = `/${document.documentElement.lang}/blog/${item.slug}`;
        }
        return {
            link,
            target: "_self",
            title: item.title,
            excerpt: item.excerpt,
            image: item.image,
            category: item.categories ? item.categories[0].name : "Uncategorized",
            categories: item.categories || [],
        };
    };

    // Shortcode Handler for [blogs]
    if (typeof Shortcode !== "undefined") {
        new Shortcode(document.querySelector(".page"), {
            blogs: function () {
                if (this.options === undefined) {
                    this.options = {};
                }
                const isWp = this.options.url ? true : false;
                let data = '<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 my-5">';
                let fetchUrl = "/api/blogs?lang=" + document.documentElement.lang;
                if (this.options.url) {
                    fetchUrl = this.options.url + "/wp-json/wp/v2/posts?_embed";
                }
                const filters = {
                    context: this.options.context,
                    page: this.options.page,
                    per_page: this.options.per_page || 6,
                    search: this.options.search,
                    after: this.options.after,
                    author: this.options.author,
                    author_exclude: this.options.author_exclude,
                    before: this.options.before,
                    exclude: this.options.exclude,
                    include: this.options.include,
                    offset: this.options.offset,
                    order: this.options.order,
                    orderby: this.options.orderby,
                    slug: this.options.slug,
                    status: this.options.status,
                    categories: this.options.categories,
                    categories_exclude: this.options.categories_exclude,
                    tags: this.options.tags,
                    tags_exclude: this.options.tags_exclude,
                    sticky: this.options.sticky,
                };
                Object.keys(filters).forEach((key) => {
                    if (filters[key]) {
                        fetchUrl += `&${key}=${filters[key]}`;
                    }
                });
                fetch(fetchUrl)
                    .then((response) => response.json())
                    .then((blogs) => {
                        blogs.forEach((item) => {
                            const { link, target, title, excerpt, image, category, categories } = extractBlogData(isWp, item);
                            let categoryHtml = `<span class="absolute top-5 left-5 bg-black text-white px-3 py-1 rounded-full text-sm">${category}</span>`;
                            if (categories.length > 0) {
                                categoryHtml = `<div class="absolute top-5 left-5">`;
                                categories.forEach((category) => {
                                    if (isWp) {
                                        categoryHtml += `<span class="ml-1 bg-black text-white px-3 py-1 rounded-full text-sm">${category.name}</span>`;
                                    } else {
                                        let category_link = `/category/${category.slug}`;
                                        if (document.URL.includes(document.documentElement.lang)) {
                                            category_link = `/${document.documentElement.lang}/category/${category.slug}`;
                                        }
                                        categoryHtml += `<a href="${category_link}" class="ml-1 bg-black text-white px-3 py-1 rounded-full text-sm">${__(category.name)}</a>`;
                                    }
                                });
                                categoryHtml += `</div>`;
                            }
                            data += `
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
                                    <article class="relative">
                                        <img class="rounded-t-lg h-60 w-full object-cover" src="${image}" alt="featured_image">
                                        ${categoryHtml}
                                        <a href="${link}" target="${target}" class="block p-4 xl:p-6 2xl:p-8 space-y-2">
                                            <h4 class="font-semibold text-lg m-0">${title}</h4>
                                            <p class="text-sm text-gray-600">${excerpt}</p>
                                        </a>
                                    </article>
                                </div>
                            `;
                        });
                        data += "</section>";
                        document.getElementById("blogs").innerHTML = blogs.length ? data : '<div class="text-center">204 - NO CONTENT AVAILABLE</div>';
                    });
                return `<div id='blogs'><div class="grid grid-cols-6 gap-6"><div class="col-span-6 bg-gray-100 rounded-lg px-5 py-4 text-center"><i class="fas fa-sync-alt fa-spin"></i></div></div></div>`;
            },
            html: function () {
                const txt = document.createElement("textarea");
                txt.innerHTML = this.contents;
                return txt.value;
            },
            contact_form: function () {
                let captcha_html = "";
                let button_attributes = "type='submit'";
                if (captcha_name && captcha_name !== "off") {
                    if (captcha_name === "recaptcha2" || captcha_name === "hcaptcha") {
                        captcha_html = `<div class="g-recaptcha" data-sitekey="${site_key}"></div>`;
                    } else if (captcha_name === "recaptcha3") {
                        button_attributes = `class="g-recaptcha" data-sitekey="${site_key}" data-callback='submitContactForm' data-action='submit'`;
                    }
                }
                return `
                    <form class="contact-form pt-5 flex flex-col gap-3" action="/widget/contact" method="post">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}" />
                        <div class="flex flex-col md:flex-row items-center gap-3">
                            <div class="w-full">
                                <label class="block mb-1" for="your_name">${__("Your Name")}</label>
                                <input class="form-input rounded-md block w-full border" type="text" name="your_name" id="your_name" placeholder="${__("Enter your Name")}" required>
                            </div>
                            <div class="w-full">
                                <label class="block mb-1" for="your_email">${__("Your Email")}</label>
                                <input class="form-input rounded-md block w-full border" type="email" name="your_email" id="your_email" placeholder="${__("Enter your Email")}" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-1" for="your_message">${__("Message")}</label>
                            <textarea class="form-input rounded-md block w-full border" name="your_message" id="your_message" placeholder="${__("Enter your Message")}" required></textarea>
                        </div>
                        ${captcha_html}
                        <button class="form-input rounded-md block w-full border-0 bg-primary text-white" ${button_attributes}>${__("Send Message")}</button>
                    </form>
                `;
            },
        });
    }
})();

const submitContactForm = () => {
    document.querySelector(".contact-form").submit();
};
