<template>
    <div ref="tableWrapper" class="dcmstable-wrapper">
        <div class="dcmstable-spinner" ref="spinner">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <table ref="tableElement" class="dcmstable dcmstable-hover" border="0" cellspacing="0" cellpadding="0" :id="id" :route="route">
            <thead>
                <th ref="expandHeader" class="dcmstable-expand-header"></th>
                <slot name="headerTemplate"></slot>
            </thead>
            <tbody ref="tableBody">
                <tr v-if="tableData.length > 0 && queryError == false" v-for="(tableItem, index) in tableData">
                    <td class="dcmstable-expand" data-dcmstable-toggle-expand @click="expandRow($event, index)">
                        <label><i class="fas fa-angle-right"></i></label>
                    </td>
                    <slot name="cellTemplate" :row="tableItem"></slot>
                </tr>
                <label class="dcmstable-noresults" v-show="tableData.length <= 0 && firstInit == false && spinnerVisible == false && queryError == false ? true : false">
                    {{ noresultstext }}
                </label>
                <div class="dcmstable-error" v-show="queryError == true && firstInit == false && spinnerVisible == false ? true : false">
                    <label class="dcmstable-error-label">{{ errortext }}</label>
                    <button class="dcmstable-error-button" data-dcmstable-reload @click="resetTable()">{{ reloadtext }}</button>
                </div>
            </tbody>
        </table>
        <div class="dcmstable-controls" v-show="tableData.length > 0 && queryError == false ? true : false">
            <nav class="dcmstable-nav" ref="paginationWrapper">
                <ul class="dcmstable-pagination">
                    <li class="dcmstable-page-item"
                        :class="{ 'disabled' : parseInt(page - 1) < availablePages / availablePages}"
                        :disabled="parseInt(page - 1) < availablePages / availablePages"
                        @click="makeQuery($event, page - 1)">
                        <span class="dcmstable-page-link" :disabled="parseInt(page - 1) < availablePages / availablePages">{{ previoustext }}</span>
                    </li>

                    <li class="dcmstable-page-item" data-dcmstable-page v-for="index in availablePages"
                        v-if="(page == index || index <= availablePages && index > page) && (index <= page + navButtonsAhead)"
                        :class="{ 'active' : page == index}"
                        @click="makeQuery($event, index)"
                    >
                        <a class="dcmstable-page-link">{{ index }}</a>
                    </li>

                    <li class="dcmstable-page-item"
                        :class="{ 'disabled' : page == availablePages}"
                        :disabled="page == availablePages"
                        @click="makeQuery($event,page + 1)">
                        <span class="dcmstable-page-link" :disabled="page == availablePages">{{ nexttext }}</span>
                    </li>
                </ul>
            </nav>
            <div class="dcmstable-perpage" ref="perPageWrapper">
                <div class="dcmstable-perpage-flex" v-show="tableData.length > 0 && queryError == false ? true : false">
                    <label class="dcmstable-perpage-label" v-bind:total="total" v-bind:perPage="perpage" v-bind:page="page">
                        {{ showingtext }} {{ (parseInt(perpage) * parseInt(page-1)) }} - {{ (parseInt(perpage) * parseInt(page-1)) + parseInt(perpage) }} {{ oftext }} {{ total }} {{ recordstext }}
                    </label>
                    <slimselect :id="id+'perPage'" :name="id+'perPage'" class="dcmstable-perpage-select" data-dcmstable-perpage>
                        <option :selected="perpage == 5" value="5">5</option>
                        <option :selected="perpage == 10" value="10">10</option>
                        <option :selected="perpage == 20" value="20">20</option>
                        <option :selected="perpage == 50" value="50">50</option>
                        <option :selected="perpage == 100" value="100">100</option>
                    </slimselect>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["route", "id"],
    name: "DataTable",
    data() {
        return Object.assign({
                showingtext: Lang('Showing'),
                oftext: Lang('of'),
                recordstext: Lang('records'),
                previoustext: Lang('Previous'),
                nexttext: Lang('Next'),
                noresultstext: Lang('No records found.'),
                errortext: Lang('An unknown error has occurred.') + " " + Lang('Contact support if this problem persists.'),
                reloadtext: Lang('Refresh table'),
                navButtonsAhead: 4,
                tableWidth: 0,
                firstInit: true,
                tableHeaders: {},
                tableHead: {},
                tableData: [],
                isSmall: false,
                total: 0,
                perpage: 10,
                page: 1,
                availablePages: 1,
                query: '',
                allowQuery: true,
                queryFilters: [],
                querySorting: {},
                queryError: false,
                spinnerVisible: true
            },
            this.$attrs
        );
    },

    // when the page has loaded
    mounted() {
        this.makeTable();
        this.assignEvents();
    },

    methods: {
        makeDelay(ms) {
            let timer = 0;
            return function(callback){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        },
        loadAnimation(){
            let spinner = this.$refs.spinner;
            this.spinnerVisible = true;
            spinner.style.transition = '0s';

            spinner.style.top = "75px";
            if (this.tableData.length > 0 && this.perpage == 5){
                spinner.style.top = "190px";
            } else if (this.tableData.length > 0 && this.perpage == 10){
                spinner.style.top = "300px";
            } else if (this.tableData.length > 0 && this.perpage == 20){
                spinner.style.top = "600px";
            }

            let spinnerDelay = this.makeDelay(50);
            spinnerDelay(function(){
                spinner.style.transition = '0.8s';
                spinner.style.opacity = 1;
            });

            this.$refs.tableBody.style.opacity = 0.25;
        },
        stopLoadAnimation(){
            this.$refs.spinner.style.opacity = 0;
            this.spinnerVisible = false;
            this.$refs.tableBody.style.opacity = 1;
        },
        resetTable(){
            this.page = 1;
            this.perpage = 10;
            this.querySorting = {};
            this.queryFilters = [];
            this.makeQuery();
        },
        makeQuery(event, page=null){
            let self = this;
            self.page = 1;

            // Prevent manipulating query when input is disabled
            if (event && event.target) {
                if (event.target.classList.contains('disabled')) {
                    return;
                }
            }

            if (page){
                self.page = page;
            }
            let firstChar = '&';
            this.loadAnimation();

            // Set correct per page value with this event
            if (event && event.target){
                if (event.target.dataset){
                    let controlElement = event.target;
                    if (typeof controlElement.dataset.dcmstablePerpage !== 'undefined'){
                        self.perpage = event.target.value;
                        self.page = 1;
                    }
                }
            }

            // Delay executing query to prevent unnecessary server load
            self.queryDelay(function(){
                // Add pagination query string if pagination property is set to true
                self.query = (self.pagination == 'false') ? '' : `?pagination%5Bpage%5D=${self.page}&pagination%5Bperpage%5D=${self.perpage}`;

                // Add filters to query string
                let f = 0;
                let filterStr = ``;
                for (const name in self.queryFilters) {
                    let value = self.queryFilters[name];
                    if (value){
                        if (!self.query){
                            firstChar = '?';
                        }
                        filterStr = filterStr + firstChar + "query%5B"+name+"%5D="+value;
                    }
                    f++;
                }
                self.query = self.query + filterStr;

                // Add sorting parameter to query string
                if (Object.keys(self.querySorting).length > 0){
                    let sortingStr = '';
                    firstChar = (self.query) ? '&' : '?';
                    sortingStr = sortingStr + firstChar + "&sort%5Bfield%5D="+self.querySorting.field+"&sort%5Bsort%5D="+self.querySorting.sort;
                    self.query = self.query + sortingStr;
                }

                // Clear expanded rows, send query and load data in datatable
                self.removeExpanded();
                self.loadData();
            });
        },
        loadData() {
            let self = this;

            window.axios({
                method: 'GET',
                url: this.route+this.query,
                responseType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    "Content-type": "application/json",
                }
            }).then(function (response) {
                let retrieved = response.data;
                self.queryError = false;
                self.tableData = retrieved.data;
                self.total = typeof retrieved.meta !== 'undefined' ? parseInt(retrieved.meta.total) : self.tableData.length;
                self.perpage = typeof retrieved.meta !== 'undefined' ? parseInt(retrieved.meta.perpage) : 10;
                self.page = typeof retrieved.meta !== 'undefined' ? parseInt(retrieved.meta.page) : 1;
                self.availablePages = typeof retrieved.meta !== 'undefined' ? parseInt(retrieved.meta.pages) : 1;
                self.firstInit = false;
                self.stopLoadAnimation();
            }).catch(function (error) {
                self.queryError = true;
                self.stopLoadAnimation();
            });
        },
        makeTable() {
            this.tableElement = this.$refs.tableElement;
            this.tableHead = this.$refs.tableHead;
            this.tableBody = this.$refs.tableBody;
            this.expandHeader = this.$refs.expandHeader;
            this.mediumSize = this.mediumsize;
            this.smallSize = this.smallsize;
            this.queryDelay = this.makeDelay(800);

            this.tableHeaders = this.tableElement.querySelectorAll('th');

            if (this.firstInit){
                this.tableElement.style.visibility = 'hidden';
            }

            this.render();
            this.makeQuery();
        },
        removeExpanded(){
            let expandedRows = this.$refs.tableElement.querySelectorAll('[data-dcms-expanded-row]');
            for (const r in expandedRows) {
                if (expandedRows[r].tagName == 'TR'){
                    expandedRows[r].remove();
                }
            }
            let expandedCarets = this.tableElement.querySelectorAll('[data-dcmstable-toggle-expand] label i');
            for (const e in expandedCarets) {
                if (expandedCarets[e].tagName == 'I'){
                    expandedCarets[e].classList.remove('fa-angle-down');
                    expandedCarets[e].classList.add('fa-angle-right');
                }
            }
        },
        expandRow(event, index){
            let target = event.target;
            if (target.tagName !== 'I'){
                target = target.querySelector('i');
            }
            if (target.classList.contains('fa-angle-right')){
                this.removeExpanded();
                target.classList.remove('fa-angle-right');
                target.classList.add('fa-angle-down');

                let activeRow = this.tableElement.querySelector('tr:nth-child('+(parseInt(index)+1)+')');
                let thisRowCells = activeRow.querySelectorAll('td[data-label]');
                let newRows = ``;

                // Generate readable TR elements with all data from the current row
                for (const c in thisRowCells) {
                    if (thisRowCells[c].tagName == 'TD'){
                        let cell = thisRowCells[c];
                        let header = this.tableHeaders[parseInt(c)+1];

                        let newRow =
                        `<tr data-dcms-expanded-row>
                            <td data-dcms-expanded-header>${header.textContent}</td>
                            <td data-dcms-expanded-value>${cell.innerHTML}</td>
                        </tr>`;

                        newRows += newRow;
                    }
                }

                activeRow.insertAdjacentHTML('afterend', newRows);
            } else {
                this.removeExpanded();
            }
        },
        toggleResponsive() {
            this.tableWidth = this.$refs.tableElement.getBoundingClientRect().width;
            this.paginationWrapper = this.$refs.paginationWrapper;
            this.perPageWrapper = this.$refs.perPageWrapper;

            function hideAtWidth(el) {
                return window.screen.width < el.dataset.width && el.dataset.hide !== 'false';
            }

            // Check if the datatable should render its large, medium or small size
            if (typeof this.mediumSize == 'undefined' || !parseInt(this.mediumSize)){
                this.mediumSize = 600;
            }
            if (typeof this.smallSize == 'undefined' || !parseInt(this.smallSize)){
                this.smallSize = 200;
            }
            this.isSmall = false;
            // If table switches to medium size
            if (window.screen.width < parseInt(this.mediumSize)){
                this.isSmall = true;
                this.tableElement.classList.add('dcmstable-md');
                this.paginationWrapper.classList.add('dcmstable-nav-md');
                this.perPageWrapper.classList.add('dcmstable-perpage-md');
                this.tableElement.classList.remove('dcmstable-sm');
                this.tableElement.classList.remove('dcmstable-lg');
            } else {
                this.tableElement.classList.add('dcmstable-lg');
                this.tableElement.classList.remove('dcmstable-md');
                this.paginationWrapper.classList.remove('dcmstable-nav-md');
                this.perPageWrapper.classList.remove('dcmstable-perpage-md');
            }
            // If table switches to small size
            if (window.screen.width < parseInt(this.smallSize)){
                this.isSmall = true;
                this.tableElement.classList.add('dcmstable-sm');
                this.paginationWrapper.classList.add('dcmstable-nav-sm');
                this.perPageWrapper.classList.add('dcmstable-perpage-sm');
                this.tableElement.classList.remove('dcmstable-lg');
                // If table switches to large size
            } else if (window.screen.width > parseInt(this.mediumSize)) {
                this.tableElement.classList.remove('dcmstable-sm');
                this.paginationWrapper.classList.remove('dcmstable-nav-sm');
                this.perPageWrapper.classList.remove('dcmstable-perpage-sm');
            }

            if (this.isSmall){
                this.removeExpanded();
            }

            // Autohide columns
            for (const h in this.tableHeaders) {
                let header = this.tableHeaders[h];
                if (header.tagName == 'TH' && this.firstInit == false) {
                    // Only hide headers if table rendered large size
                    if (this.isSmall == false){
                        // If header is no longer fully visible in viewport
                        if (hideAtWidth(header)){
                            header.style.display = 'none';
                            let cells = this.tableElement.querySelectorAll('tr > td:nth-child('+(parseInt(h)+1)+')');
                            for (const c in cells) {
                                if (cells[c].tagName == 'TD' && cells[c].dataset && typeof cells[c].dataset.dcmsToggleExpand == 'undefined'){
                                    cells[c].style.display = 'none';
                                }
                            }
                        }
                        // Show header if it fits in the width of the viewport
                        else {
                            header.style.display = 'revert';
                            let cells = this.tableElement.querySelectorAll('tr > td:nth-child('+(parseInt(h)+1)+')');
                            for (const c in cells) {
                                if (cells[c].tagName == 'TD' && cells[c].dataset && typeof cells[c].dataset.dcmsToggleExpand == 'undefined'){
                                    cells[c].style.display = 'revert';
                                }
                            }
                        }
                    } else {
                        // Set header and cells display to flow-root when table has rendered medium or small size
                        header.style.display = 'flow-root';
                        let cells = this.tableElement.querySelectorAll('tr > td:nth-child('+(parseInt(h)+1)+')');
                        for (const c in cells) {
                            if (cells[c].tagName == 'TD' && cells[c].dataset && typeof cells[c].dataset.dcmsToggleExpand == 'undefined'){
                                cells[c].style.display = 'flow-root';
                            }
                        }
                    }
                }
            }
        },
        render() {
            let self = this;

            // Render responsive datable if conditions are met
            this.toggleResponsive();
            window.addEventListener('resize', function(){
                self.toggleResponsive();
            });

            if (this.firstInit){
                this.tableElement.style.visibility = 'inherit';
            }

            this.tableElement.DCMSTable = this;
        },
        removeOtherSorters(clickedSorter){
            this.$refs.tableWrapper.querySelectorAll('th').forEach((sorter) => {
                if (sorter !== clickedSorter && sorter.querySelector('i')) {
                    sorter.querySelector('i').remove();
                }
            });
        },
        assignEvents(){
            let self = this;

            // Wait for document to load, then assign filtering, searching and sorting events
            let readyStateCheckInterval = setInterval(function () {
                if (document && (document.readyState == 'complete')) {
                    clearInterval(readyStateCheckInterval);

                    // Per page
                    self.$refs.tableWrapper.querySelector('[data-dcmstable-perpage]').addEventListener('change',(event) => {
                        self.perpage = event.target.value;
                        self.makeQuery();
                    })

                    // Query filters
                    Array.from(document.querySelectorAll('[data-dcmstable-filter]')).forEach((filter) => {
                        if (self.$refs.tableWrapper.querySelector(filter.dataset.dcmstable)){
                            if (filter.dataset.event){
                                filter.addEventListener(filter.dataset.event,function(){
                                    self.queryFilters[filter.name] = filter.value;
                                    self.makeQuery();
                                });
                            } else {
                                filter.addEventListener('change',function(){
                                    self.queryFilters[filter.name] = filter.value;
                                    self.makeQuery();
                                });
                            }
                        }
                    });

                    // Sorting
                    let sortAscIcon = 'fa-long-arrow-alt-down';
                    let sortDescIcon = 'fa-long-arrow-alt-up';
                    let sortAscHtml = `<i class="fas ${sortAscIcon} dcmstable-sort"></i>`;
                    let sortDescHtml = `<i class="fas ${sortDescIcon} dcmstable-sort"></i>`;
                    self.$refs.tableWrapper.querySelectorAll('th:not([data-sort="false"])').forEach((sorter) => {
                        sorter.addEventListener('click',function(){
                            self.removeOtherSorters(sorter);
                            if (sorter.querySelector('i')){
                                if (sorter.querySelector('i').classList.contains(sortAscIcon)){
                                    sorter.querySelector('i').remove();
                                    sorter.innerHTML = sorter.innerHTML + sortDescHtml;
                                    self.querySorting = {
                                        field: sorter.dataset.field,
                                        sort: 'desc'
                                    };
                                } else if (sorter.querySelector('i').classList.contains(sortDescIcon)) {
                                    sorter.querySelector('i').remove();
                                    self.querySorting = {};
                                }
                            } else {
                                sorter.innerHTML = sorter.innerHTML + sortAscHtml;
                                self.querySorting = {
                                    field: sorter.dataset.field,
                                    sort: 'asc'
                                };
                            }
                            self.makeQuery();
                        })
                    });

                    // General search
                    Array.from(document.querySelectorAll('[data-dcmstable-search]')).forEach((search) => {
                        if (self.$refs.tableWrapper.querySelector(search.dataset.dcmstable)){
                            search.addEventListener('keyup',function(){
                                self.queryFilters['generalSearch'] = search.value;
                                self.makeQuery();
                            })
                        }
                    });
                }
            }, 100);
        }
    },
}
</script>

<style scoped>

</style>
