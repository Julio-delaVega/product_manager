<template>
    <div class="container py-5">
        <div class="card">
            <div class="card-header">
                <h2>Manage Products</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <button
                            class="btn btn-primary"
                            data-toggle="modal"
                            data-target="#product-modal"
                            @click="newProduct"
                        >
                            <i class="fas fa-plus-circle"></i> Add New
                        </button>
                    </div>
                    <div class="col-md-9">
                        <div class="form-inline justify-content-end">
                            <div class="form-group">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Search..."
                                        v-model="filters.name"
                                    />
                                    <div class="input-group-append">
                                        <button
                                            class="btn btn-outline-secondary"
                                            v-show="searching"
                                            type="button"
                                            @click="clearSearch"
                                        >
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th @click="sort('name')" :class="classes('name')">
                                Product Name
                            </th>
                            <th
                                @click="sort('category')"
                                :class="classes('category')"
                            >
                                Category
                            </th>
                            <th
                                class="text-right"
                                @click="sort('price')"
                                :class="classes('price')"
                            >
                                Price
                            </th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(product, index) in productsPaginated"
                            :key="index"
                        >
                            <td>{{ product.name }}</td>
                            <td>{{ product.category }}</td>
                            <td class="text-right">
                                {{ product.price.toFixed(2) }}
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    @click="editProduct(product)"
                                    data-toggle="modal"
                                    data-target="#product-modal"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteProduct(product)"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center">
                        <li
                            class="page-item"
                            :class="{ disabled: firstLastPage == 'first' }"
                        >
                            <a
                                class="page-link"
                                href="#"
                                @click.prevent="prevPage"
                                >Previous</a
                            >
                        </li>
                        <li
                            v-for="(page, index) in pages"
                            :key="index"
                            class="page-item"
                            :class="{ active: page == currPage }"
                        >
                            <a
                                class="page-link"
                                href="#"
                                @click.prevent="setPage(page)"
                            >
                                {{ page }}
                            </a>
                        </li>
                        <li
                            class="page-item"
                            :class="{ disabled: firstLastPage == 'last' }"
                        >
                            <a
                                class="page-link"
                                href="#"
                                @click.prevent="nextPage"
                                >Next</a
                            >
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Modal -->
        <div
            id="product-modal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="exampleModalLabel"
            aria-hidden="true"
            ref="productModal"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            {{ modalTitle }}
                        </h5>
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="saveProduct">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="focusedProduct.name"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select
                                    class="form-control"
                                    v-model="focusedProduct.category"
                                    required
                                >
                                    <option
                                        v-for="(category, index) in categories"
                                        :key="index"
                                        :value="category"
                                    >
                                        {{ category }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price ($)</label>
                                <input
                                    type="number"
                                    step="any"
                                    class="form-control"
                                    v-model.number="focusedProduct.price"
                                    required
                                />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-dismiss="modal"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Save product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            products: [],
            order: {
                dir: 1,
                colName: "name"
            },
            filters: {
                name: ""
            },
            perPage: 10,
            currPage: 1,
            focusedProduct: {
                id: null,
                name: null,
                category: null,
                price: null
            },
            editing: false
        };
    },
    computed: {
        productsFiltered() {
            var products = this.products;
            var search = this.filters.name;
            if (search.length > 0) {
                var regex = new RegExp(search, "i");
                products = products.filter(p => p.name.match(regex));
            }
            return products;
        },
        productsSorted() {
            var colName = this.order.colName;
            var dir = this.order.dir;
            return this.productsFiltered.sort((a, b) => {
                var left = a[colName];
                let right = b[colName];
                if (isNaN(left) || isNaN(right)) {
                    if (left < right) {
                        return -1 * dir;
                    } else if (left > right) {
                        return dir;
                    } else {
                        return 0;
                    }
                } else {
                    return (a[colName] - b[colName]) * dir;
                }
            });
        },
        productsPaginated() {
            var start = (this.currPage - 1) * this.perPage;
            var end = this.currPage * this.perPage;
            return this.productsSorted.slice(start, end);
        },
        sortType() {
            return this.order.dir == 1 ? "ascending" : "descending";
        },
        searching() {
            return this.filters.name.length > 0;
        },
        pages() {
            return Math.ceil(this.productsSorted.length / this.perPage);
        },
        firstLastPage() {
            if (this.currPage == 1) {
                return "first";
            }
            if (this.currPage == this.pages) {
                return "last";
            }
            return null;
        },
        categories() {
            var categories = this.products.map(p => p.category);
            return Array.from(new Set(categories)).sort((left, right) => {
                if (left < right) {
                    return -1;
                } else if (left > right) {
                    return 1;
                } else {
                    return 0;
                }
            });
        },
        modalTitle() {
            return this.editing ? "Update Product" : "Add New Product";
        }
    },
    created() {
        this.fetchProducts();
    },
    methods: {
        fetchProducts() {
            axios.get("/api/products").then(res => {
                this.products = res.data.data;
            });
        },
        sort(colName) {
            this.order.colName = colName;
            this.order.dir *= -1;
        },
        classes(colName) {
            return [
                "sort-control",
                colName == this.order.colName ? this.sortType : ""
            ];
        },
        clearSearch() {
            this.filters.name = "";
        },
        prevPage() {
            if (this.firstLastPage != "first") {
                this.currPage--;
            }
        },
        nextPage() {
            if (this.firstLastPage != "last") {
                this.currPage++;
            }
        },
        setPage(page) {
            this.currPage = page;
        },
        newProduct() {
            this.focusedProduct = {
                id: null,
                name: null,
                category: null,
                price: null
            };
            this.editing = false;
        },
        editProduct(product) {
            this.focusedProduct = Object.assign({}, product);
            this.editing = true;
        },
        saveProduct() {
            if (this.editing) {
                var index = this.products.findIndex(
                    p => p.id == this.focusedProduct.id
                );
                this.$set(this.products, index, this.focusedProduct);
            } else {
                var id = this.products.reduce((max, p) => {
                    return p.id > max ? p.id : max;
                }, 0);
                id++;
                this.focusedProduct.id = id;
                this.products.push(this.focusedProduct);
            }
            this.focusedProduct = {
                id: null,
                name: null,
                category: null,
                price: null
            };
            this.editing = false;
            $(this.$refs.productModal).modal("hide");
        },
        deleteProduct(product) {
            var sure = confirm(
                `Are you sure you want to delete the product with name "${product.name}"`
            );
            if (sure) {
                var index = this.products.findIndex(p => p.id == product.id);
                this.products.splice(index, 1);
            }
        }
    },
    watch: {
        "order.colName": {
            handler() {
                this.order.dir = 1;
            }
        }
    }
};
</script>
