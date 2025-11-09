@extends('customer.layouts.app', ['tab_name' => 'Shop'])

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
                    <li class="breadcrumb-item active">Shop</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section-6 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 sidebar">
                    <div class="sub-title">
                        <h2>Categories</h3>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="accordion accordion-flush" id="accordionExample">
                                @foreach ($categories as $key => $category)
                                    <div class="accordion-item">
                                        @if ($category->sub_categories->isNotEmpty())
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $key }}"
                                                    aria-expanded="false" aria-controls="collapse{{ $key }}">
                                                    {{ $category->name }}
                                                </button>
                                            </h2>
                                            <div id="collapse-{{ $key }}" class="accordion-collapse collapse {{ $categorySlug == $category->slug ? 'show' : '' }}"
                                                aria-labelledby="headingOne" data-bs-parent="#accordionExample"
                                                style="">
                                                <div class="accordion-body">
                                                    <div class="navbar-nav">
                                                        @foreach ($category->sub_categories as $subCategory)
                                                            <a href="{{ route('customer.shop', ['categorySlug' => $category->slug, 'subCategorySlug' => $subCategory->slug]) }}"
                                                                class="nav-item nav-link {{ $subCategorySlug == $subCategory->slug ? 'text-primary' : '' }}">{{ $subCategory->name }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('customer.shop', ['categorySlug' => $category->slug]) }}" class="nav-item nav-link  {{ $categorySlug == $category->slug ? 'text-primary' : '' }}">{{ $category->name }}</a>
                                        @endif

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="sub-title mt-5">
                        <h2>Brand</h3>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            @foreach ($brands as $brand)
                                <div class="form-check mb-2">
                                    <input class="form-check-input brand-label" type="checkbox" name="brand[]"
                                        value="{{ $brand->id }}" id="brand-{{ $brand->id }}" {{ in_array($brand->id, $brandsArray) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="brand-{{ $brand->id }}">
                                        {{ $brand->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="sub-title mt-5">
                        <h2>Price</h3>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <input type="text" class="js-range-slider" name="my_range" value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row pb-3">
                        <div class="col-12 pb-1">
                            <div class="d-flex align-items-center justify-content-end mb-4">
                                <div class="ml-2">
                                    <select name="sort" id="sort" class="form-control">
                                        <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Latest</option>
                                        <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Price High</option>
                                        <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>Price Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @foreach ($products as $product)
                            @php
                                $image = $product->images->first()->getSmallImage();
                            @endphp
                            <div class="col-md-4">
                                <div class="card product-card">
                                    <div class="product-image position-relative">
                                        <a href="" class="product-img"><img class="card-img-top"
                                                src="{{ $image }}" alt=""></a>
                                        <a class="whishlist" href="222"><i class="far fa-heart"></i></a>

                                        <div class="product-action">
                                            <a class="btn btn-dark" href="#">
                                                <i class="fa fa-shopping-cart"></i> Add To Cart
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body text-center mt-3">
                                        <a class="h6 link" href="product.php">{{ $product->title }}</a>
                                        <div class="price mt-2">
                                            <span
                                                class="h5"><strong>{{ $product->formattedPrice() }}</strong></span>
                                            @if ($product->compare_price > 0)
                                                <span
                                                    class="h6 text-underline"><del>{{ $product->formattedComparePrice() }}</del></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-md-12 pt-5">
                            {{-- include search param in url with pagination --}}
                            {{ $products->withQueryString()->links() }}
                            {{-- <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-end">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1"
                                            aria-disabled="true">Previous</a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        initFilters({
            url: '{{ url()->current() }}',
            checkboxSelectorMap: {
                brands: '.brand-label',
            },
            sliderSelectorMap: {
                price: '.js-range-slider',
            },
            sliderOptions: {
                from: "{{ $priceMin }}",
                to: "{{ $priceMax }}",
            },
            selectSelectorMap:{
                sort: '#sort',
            }
        });
    </script>
@endsection