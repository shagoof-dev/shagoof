<style>
    .trending-products-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    @media (max-width: 1599px) {
        .trending-products-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media (max-width: 1199px) {
        .trending-products-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 991px) {
        .trending-products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 767px) {
        .trending-products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }

    @media (max-width: 479px) {
        .trending-products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }

    .trending-products-grid .ps-product {
        width: 100%;
    }
</style>

<div class="ps-product-list mt-40 mb-40">
    <div class="ps-container">
        <div class="ps-section__header">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
            <ul class="ps-section__links">
                <li><a href="{{ route('public.products') }}">{{ __('View All') }}</a></li>
            </ul>
        </div>
        <div class="ps-section__content">
            <div class="trending-products-grid">
                @foreach($products as $product)
                    <div class="ps-product">
                        {!! Theme::partial('product-item', compact('product')) !!}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
