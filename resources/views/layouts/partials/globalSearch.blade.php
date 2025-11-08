{{-- global search modal --}}
<style>
    /* Light Mode (Default) - These styles apply when the system is in light mode or no preference is set */
    body {
        /* Uncomment if you want to apply body styles here */
        /* font-family: 'Inter', sans-serif; */
        /* background-color: #f3f6f9; */
    }

    .search-btn-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .modal.search-modal .modal-dialog {
        max-width: 600px;
        margin-top: 5rem;
    }

    .modal.search-modal .modal-content {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        background-color: #fff;
        /* Default white background for modal content */
    }

    .modal.search-modal .modal-header {
        border-bottom: none;
        padding: 1.5rem 1.5rem 0 1.5rem;
    }

    .modal.search-modal .modal-body {
        padding: 1rem 0;
        min-height: 200px;
        max-height: 400px;
    }

    .modal.search-modal .modal-footer {
        border-top: 1px solid #eff2f5;
        padding: 1rem 1.5rem;
        background-color: #f9f9f9;
        /* Light grey footer */
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    /* Search input styling */
    .search-input-group {
        position: relative;
    }

    .search-input-group .search-icon {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: #a1a5b7;
        /* Light grey icon */
    }

    .search-input-group .form-control {
        border-radius: 0.5rem;
        background-color: #f9f9f9;
        /* Light grey input background */
        border: 1px solid #eff2f5;
        /* Light border */
        padding-left: 3rem;
        height: 50px;
        font-size: 1rem;
        color: #5e6278;
        /* Dark text for light mode */
    }

    .search-input-group .form-control:focus {
        background-color: #fff;
        /* White background on focus */
        border-color: #007bff;
        /* Blue border on focus */
        box-shadow: none;
    }

    .no-recent-searches {
        text-align: center;
        padding: 3rem 0;
        color: #a1a5b7;
    }

    .search-footer-help {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        font-size: 0.85rem;
        color: #7e8299;
    }

    .key-combo {
        background-color: #e4e6ef;
        /* Light key combo background */
        padding: 0.2rem 0.5rem;
        border-radius: 0.3rem;
        font-weight: 600;
        color: #5e6278;
        /* Dark text for key combo */
        margin-left: 0.5rem;
    }

    .search-results .list-group-item {
        border: none;
        padding: 0.75rem 1.5rem;
        background-color: transparent;
        /* Ensure no default background interferes */
    }

    .search-results .list-group-item .result-title {
        font-weight: 600;
        color: #343a40;
        /* Default text color */
    }

    .search-results .list-group-item .result-group {
        font-size: 0.8rem;
        color: #a1a5b7;
    }

    .search-results .list-group-item.active {
        background-color: #f1faff;
        color: #009ef7;
    }

    .search-results .list-group-item:hover {
        background-color: #f8f9fa;
        /* Hover background */
    }


    /* Dark Mode Styles */

    [data-bs-theme="dark"] .modal.search-modal .modal-content {
        background-color: #2d3748;
        /* Darker background for modal content */
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.3);
    }

    [data-bs-theme="dark"] .modal.search-modal .modal-footer {
        border-top: 1px solid #4a5568;
        background-color: #28303f;
        /* Even darker footer */
    }

    [data-bs-theme="dark"] .search-input-group .search-icon {
        color: #a0aec0;
        /* Lighter icon for dark mode */
    }

    [data-bs-theme="dark"] .search-input-group .form-control {
        background-color: #252a35;
        /* Darker input background */
        border: 1px solid #4a5568;
        /* Darker border */
        color: #e2e8f0;
        /* Lighter text for dark mode */
    }

    [data-bs-theme="dark"] .search-input-group .form-control:focus {
        background-color: #2d3748;
        /* Slightly lighter dark background on focus */
        border-color: #63b3ed;
        /* Lighter blue border on focus */
    }

    [data-bs-theme="dark"] .no-recent-searches {
        color: #a0aec0;
        /* Lighter grey for no results message */
    }

    [data-bs-theme="dark"] .search-footer-help {
        color: #cbd5e0;
        /* Lighter text for footer help */
    }

    [data-bs-theme="dark"] .key-combo {
        background-color: #4a5568;
        /* Darker key combo background */
        color: #e2e8f0;
        /* Lighter text for key combo */
    }

    [data-bs-theme="dark"] .search-results .list-group-item .result-title {
        color: #e2e8f0;
        /* Lighter text for result titles */
    }

    [data-bs-theme="dark"] .search-results .list-group-item .result-group {
        color: #a0aec0;
        /* Lighter text for result groups */
    }

    [data-bs-theme="dark"] .search-results .list-group-item.active {
        background-color: #3b5a7a;
        /* Darker active background */
        color: #90cdf4;
        /* Lighter active text */
    }

    [data-bs-theme="dark"] .search-results .list-group-item:hover {
        background-color: #3b4d63;
        /* Darker hover background */
    }
</style>

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        function globalSearch() {
            return {
                query: '',
                results: [],
                isLoading: false,
                selectedIndex: -1,
                modal: null,
                init() {
                    // Initialize the Bootstrap modal instance
                    this.modal = new bootstrap.Modal(document.getElementById('globalSearchModal'));

                    // Focus input when modal opens
                    document.getElementById('globalSearchModal').addEventListener('shown.bs.modal', () => {
                        this.$refs.searchInput.focus();
                    });

                    // Watch for changes in the query property
                    this.$watch('query', (value) => {
                        if (value.length < 2) {
                            this.results = [];
                            this.isLoading = false;
                            return;
                        }

                        this.isLoading = true;
                        // Fetch results from your Laravel backend
                        fetch(`/global-search?q=${value}`)
                            .then(response => response.json())
                            .then(data => {
                                this.results = data;
                                this.isLoading = false;
                                this.selectedIndex = -1; // Reset selection
                            })
                            .catch(() => {
                                this.isLoading = false;
                                // You could add error handling here
                            });
                    });
                },
                openModal() {
                    this.modal.show();
                },
                closeModal() {
                    this.modal.hide();
                },
                selectNext() {
                    if (this.selectedIndex < this.results.length - 1) {
                        this.selectedIndex++;
                    }
                },
                selectPrevious() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                    }
                },
                goToResult() {
                    if (this.selectedIndex > -1 && this.results[this.selectedIndex]) {
                        window.location = this.results[this.selectedIndex].url;
                    }
                }
            }
        }
    </script>
@endpush

<!-- The Global Search Modal -->
<div class="modal fade search-modal" id="globalSearchModal" tabindex="-1" aria-hidden="true" x-data="globalSearch()"
    @keydown.escape.window="closeModal()" @keydown.ctrl.k.window.prevent="openModal()">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="search-input-group w-100">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="Search..." x-model.debounce.300ms="query"
                        @keydown.down.prevent="selectNext()" @keydown.up.prevent="selectPrevious()"
                        @keydown.enter.prevent="goToResult()" x-ref="searchInput">
                </div>
            </div>
            <div class="modal-body overflow-y-scroll">
                <!-- Loading State -->
                <template x-if="isLoading">
                    <div class="d-flex justify-content-center align-items-center h-100 py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </template>

                <!-- No Query State -->
                <template x-if="!query && !isLoading">
                    <div class="no-recent-searches">
                        <p>Search for Users and etc.</p>
                    </div>
                </template>

                <!-- Results State -->
                <template x-if="results.length > 0 && !isLoading">
                    <div class="search-results">
                        <ul class="list-group list-group-flush">
                            <template x-for="(result, index) in results" :key="index">
                                <a :href="result.url" class="list-group-item list-group-item-action"
                                    :class="{ 'active': selectedIndex === index }">
                                    <div class="d-flex w-100 justify-content-between">
                                        <span class="result-title" x-html="result.highlighted"></span>
                                        <span class="result-group" x-text="result.group"></span>
                                    </div>
                                    <template x-if="result.match_info">
                                        <small class="match-info mt-1" x-html="result.match_info"></small>
                                    </template>
                                </a>
                            </template>
                        </ul>
                    </div>
                </template>

                <!-- No Results State -->
                <template x-if="query && results.length === 0 && !isLoading">
                    <div class="no-recent-searches">
                        <p>No results found for "<strong x-text="query"></strong>"</p>
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <div class="search-footer-help">
                    <div class="d-flex align-items-center">
                        <span><span class="key-combo">↵</span> to select</span>
                        <span class="ms-3"><span class="key-combo">↑</span><span class="key-combo">↓</span> to
                            navigate</span>
                        <span class="ms-3"><span class="key-combo">esc</span> to close</span>
                    </div>
                    <div class="search-by">
                        <span>Search by Nazdev</span>
                        <a href="#" class="ms-2">
                            <img src="{{ asset('certify.png') }}" alt="Nazdev" height="35">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
