;(function (window, document, undefined) {
    var report = function () {
        var form = document.createElement("form");
        if (form.reportValidity) {
            return true
        }
        return false
    };
    if (!report()) {
        Object.defineProperty(HTMLFormElement.prototype, "reportValidity", {
            get: function () {
                var that = this;
                return function () {
                    if (!that.reportValidityFakeSubmit) {
                        that.reportValidityFakeSubmit = document.createElement("button");
                        that.reportValidityFakeSubmit.setAttribute("type", "submit");
                        that.reportValidityFakeSubmit.setAttribute("hidden", "hidden");
                        that.reportValidityFakeSubmit.setAttribute("style", "display:none");
                        that.reportValidityFakeSubmit.setAttribute("class", "reportValidityFakeSubmit");
                        that.reportValidityFakeSubmit.addEventListener("click", function (evt) {
                            if (that.checkValidity()) {
                            }
                        });
                        that.appendChild(that.reportValidityFakeSubmit)
                    }
                    that.reportValidityFakeSubmit.click();
                    return false
                }
            }, configurable: true,
        })
    }
})(window, document);
