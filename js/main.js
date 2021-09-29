document.addEventListener("DOMContentLoaded", () => {
    var ajaxSend = async (formData, url) => {
        var ajaxResponse = await fetch(url, {
            method: "POST",
            body: formData,
        });
        if (!ajaxResponse.ok) {
            throw new Error(`Ошибка по адресу ${url}, статус ошибки ${ajaxResponse.status}`);
        }
        return await ajaxResponse.text();
    };

    var forms = document.querySelectorAll("form");
    forms.forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            ajaxSend(formData, form.action)
                .then((response) => {
                    form.nextElementSibling.innerHTML = response;
                })
                .catch((err) => console.error(err));
        });
    });
});