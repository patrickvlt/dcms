<template>
  <div ref="tableWrapper">
    <div class="form-group">
      <table ref="tableElement" data-type="jexcel" :id="id">
        <tr>
            <slot name="headerTemplate"></slot>
        </tr>
      </table>
      <span class="form-text text-muted">{{ smalltext }}</span>
    </div>
    <div class="form-group">
      <button id="submitTable" type="submit" submit-jexcel class="btn btn-primary mr-2">
        {{ importbuttontext }}
      </button>
      <button id="fixSheet" type="button" autocorrect-jexcel class="btn btn-secondary mr-2">
        {{ autocorrectbuttontext }}
      </button>
    </div>
  </div>
</template>
<script>
export default {
    props: [
        "autocorrectbuttontext",
        "importbuttontext",
        "autocorrectroute",
        "emptyrows",
        "autocorrectroute",
        "smalltext",
    ],

    data() {
        return Object.assign(
            {
                tableElement: {},
                tableWrapper: {},
                tableHeaders: [],
                sheetData: "",
                sheetColumns: [],
            },
            this.$attrs
        );
    },

    mounted() {
        if (typeof jexcel == 'undefined' && document.querySelectorAll('[data-type="jexcel"]').length > 0 && (window.DCMS.config.plugins.jexcel && window.DCMS.config.plugins.jexcel.enable !== false)) {
            window.DCMS.loadCSS(window.DCMS.config.plugins.jexcel);
            window.DCMS.loadJS(window.DCMS.config.plugins.jexcel);
        }
        if (typeof jsuites == 'undefined' && document.querySelectorAll('[data-type="jexcel"]').length > 0 && (window.DCMS.config.plugins.jsuites && window.DCMS.config.plugins.jsuites.enable !== false)) {
            window.DCMS.loadCSS(window.DCMS.config.plugins.jsuites);
            window.DCMS.loadJS(window.DCMS.config.plugins.jsuites);
        }

        this.makeTable();
    },

    methods: {
        makeColumn(header, ajax = null) {
            let column = {};

            column.type = header.dataset.type;
            if (ajax) {
                column.source = ajax;
            }
            column.title = header.dataset.label;
            column.width = header.dataset.width;
            column.tableoverflow = true;
            column.autocomplete = header.dataset.autocomplete == "true" ? "true" : "false";
            if (header.dataset.type == "calendar") {
                column.options = {
                    format: header.dataset.format ? header.dataset.format : window.AppDateFormat,
                };
            }

            this.sheetColumns.push(column);
        },
        clearInvalid() {
            let self = this;

            function CleanElement(element) {
                if (element.classList.contains("invalid")) {
                    element.classList.remove("invalid");
                }
            }
            let formRows = Array.from(
                self.tableWrapper.querySelector("tbody").getElementsByTagName("tr")
            );
            formRows.forEach(function (element) {
                CleanElement(element);
                Array.from(element.getElementsByTagName("td")).forEach((element) =>
                    CleanElement(element)
                );
            });
        },
        addAutoCorrect() {
            let self = this;
            self.tableWrapper.querySelector("[autocorrect-jexcel]").addEventListener("click", function (e) {
                    let dropdownHeaders = [];
                    Array.from(self.tableHeaders).forEach((th) => {
                        if (th.dataset.jexcelType == "dropdown") {
                            dropdownHeaders.push({
                                column: th.cellIndex,
                                text: th.textContent,
                            });
                        }
                    });
                    window.axios({
                            method: "POST",
                            url: self.autocorrectroute,
                            data: {
                                data: self.sheetData,
                                th: dropdownHeaders,
                            },
                            responseType: "json",
                            headers: {
                                "X-CSRF-TOKEN": window.DCMS.csrf,
                                "Content-type": "application/x-www-form-urlencoded",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                        }).then(function (response) {
                            Swal.fire({
                                title: Lang("Are you sure?"),
                                html: Lang("This will try to fix empty dropdown columns.") + "<br>" + Lang("Do you want to continue?"),
                                icon: "warning",
                                showCancelButton: true
                            }).then(function (result) {
                                if (result.value) {
                                    for (const t in window.DCMS.jExcel.tables) {
                                        let currentTable = self.tableWrapper.querySelector("table");
                                        let jExcelTable = window.DCMS.jExcel.tables[t];
                                        if (jExcelTable.el == currentTable) {
                                            jExcelTable.setData(response.data, false);
                                            window.toastr.success(Lang("Sheet has been updated."));
                                        }
                                    }
                                }
                            });
                        }).catch(function () {
                            Swal.fire({
                                title: Lang("Data correction failed"),
                                text: Lang("The provided data couldn't be fixed."),
                                icon: "error",
                                confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== "undefined") ? window.DCMS.sweetAlert.confirmButtonText: Lang("OK"),
                            });
                        });
                });
        },
        initjExcel(tableToMake) {
            // construct table
            let self = this;
            let dataFill = [];

            for (let i = 0; i < parseInt(self.emptyrows); i++) {
                dataFill.push("");
            }

            let table = jexcel(tableToMake, {
                columns: self.sheetColumns,
                data: dataFill,
                columnDrag: true,
                colWidths: self.sheetColumns.map(function (el) {
                    return el.width ? el.width : 100;
                }),
                allowInsertColumn: false,
                allowManualInsertColumn: false,
                text: window.DCMS.jExcel.translations,
            });

            if (self.tableElement) {
                self.tableElement.style.display = "block";
            }

            window.DCMS.jExcel.tables.push(table);
            self.clearInvalid();

            if (self.tableElement) {
                self.tableWrapper.closest("form").addEventListener("submit", function (e) {
                    e.preventDefault();
                    self.clearInvalid(e);
                    self.sheetData = table.getData();

                    window.axios({
                            method: "POST",
                            url: e.target.action,
                            data: self.sheetData,
                            responseType: "json",
                            headers: {
                                "X-CSRF-TOKEN": window.DCMS.csrf,
                                "Content-type": "application/x-www-form-urlencoded",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                        })
                        .then(function (response) {
                            window.toastr.success(response.data.response.message);
                            if (response.data.url) {
                                setTimeout(function () {
                                    window.location.href = response.data.url;
                                }, 2500);
                            }
                        })
                        .catch(function (error) {
                            if (error.response.data.message == "The given data was invalid.") {
                                let alertMsg = "";
                                for (const z in error.response.data.errors) {
                                    alertMsg += error.response.data.errors[z][0] + "<br>";
                                    Array.from(
                                        document.querySelectorAll("tbody tr td:not(.jexcel_row)")
                                    ).forEach(function (cell) {
                                        if (String(error.response.data.errors[z]).toLowerCase().indexOf(cell.textContent.toLowerCase()) > -1 && cell.textContent !== "") {
                                            cell.classList.add("invalid");
                                        }
                                    });
                                }
                                Swal.fire({
                                    title: Lang("Import failed"),
                                    html: alertMsg,
                                    icon: "error",
                                    confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== "undefined") ? window.DCMS.sweetAlert.confirmButtonText: Lang("OK"),
                                });
                            } else {
                                Swal.fire({
                                    title: error.response.data.response.title,
                                    html: error.response.data.response.message,
                                    icon: "error",
                                    confirmButtonText: typeof (window.DCMS.sweetAlert.confirmButtonText !== "undefined") ? window.DCMS.sweetAlert.confirmButtonText: Lang("OK"),
                                });
                            }
                        });
                });
            }
        },
        makeTable() {
            this.tableElement = this.$refs.tableElement;
            this.tableWrapper = this.$refs.tableWrapper;
            this.tableHeaders = this.$refs.tableElement.querySelectorAll('th');

            let self = this;

            window.DCMS.hasLoaded("jexcel", function () {
                // fetch information from self.tableElement headers
                for (const h in self.tableHeaders) {
                    let header = self.tableHeaders[h];
                    if (header.dataset) {
                        if (header.dataset && header.dataset.fetchRoute !== null && typeof header.dataset.fetchRoute !== "undefined") {
                            window.axios({
                                    method: "GET",
                                    url: header.dataset.fetchRoute,
                                    responseType: "json",
                                    headers: {
                                        "X-CSRF-TOKEN": window.DCMS.csrf,
                                        "Content-type": "application/x-www-form-urlencoded",
                                        "X-Requested-With": "XMLHttpRequest",
                                    },
                                })
                                .then(function (response) {
                                    self.makeColumn(header, response.data);
                                    if (self.sheetColumns.length == self.tableHeaders.length) {
                                        self.initjExcel(self.tableElement);
                                    }
                                });
                        } else if (header.dataset) {
                            self.makeColumn(header);
                            if (self.sheetColumns.length == self.tableHeaders.length) {
                                self.initjExcel(self.tableElement);
                            }
                        }
                    }
                }
                self.addAutoCorrect();
            });
        },
    },
};
</script>
<style lang="">
</style>
