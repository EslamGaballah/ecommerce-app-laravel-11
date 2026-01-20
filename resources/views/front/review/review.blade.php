<div class="row">
    <!-- ⭐ Rating Summary -->
    <div class="col-lg-4 col-12">
        <div class="single-block give-review">

            <h4>
                {{ number_format($product->rating_avg, 1) }} (Overall)
            </h4>
            <p>{{ $product->rating_count }} {{ __('app.reviews') }}</p>

            <ul>
                @for($star = 5; $star >= 1; $star--)
                    <li>
                        <span>
                            {{ $star }} stars - {{ $ratingsCount[$star] ?? 0 }}
                        </span>

                        @for($i = 1; $i <= 5; $i++)
                            <i class="lni {{ $i <= $star ? 'lni-star-filled' : 'lni-star' }}"></i>
                        @endfor
                    </li>
                @endfor
            </ul>

            <!-- Button -->
            @auth
                <button type="button" class="btn review-btn" data-bs-toggle="modal"
                        data-bs-target="#reviewModal">
                    {{ __('app.leave_review') }}
                </button>
            @else
                <a href="{{ route('login') }}" class="btn review-btn">
                    {{ __('app.leave_review') }}
                </a>
            @endauth

        </div>
    </div>

    <!-- 💬 Reviews -->
    <div class="col-lg-8 col-12">
        <div class="single-block">
            <div class="reviews">
                <h4 class="title">{{ __('app.latest_reviews') }}</h4>

                @forelse($product->reviews as $review)
                    <div class="single-review">
                        <img src="https://via.placeholder.com/150x150" alt="#">

                        <div class="review-info">
                            <h4>
                                {{ Str::limit($review->review, 40) }}
                                <span>{{ $review->user->name }}</span>
                            </h4>

                            <ul class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <li>
                                        <i class="lni {{ $i <= $review->rating ? 'lni-star-filled' : 'lni-star' }}"></i>
                                    </li>
                                @endfor
                            </ul>

                            <p>{{ $review->review }}</p>
                        </div>
                    </div>
                @empty
                    <p>لا توجد مراجعات حتى الآن</p>
                @endforelse

            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->

<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('products.review', $product) }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('app.leave_review') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>{{ __('app.rating') }}</label>
                    <select name="rating" class="form-control" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} ⭐</option>
                        @endfor
                    </select>

                    <label class="mt-2">{{ __('app.leave_review') }}</label>
                    <textarea name="review" class="form-control"
                              placeholder="اكتب رأيك"></textarea>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">{{ __('app.submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

