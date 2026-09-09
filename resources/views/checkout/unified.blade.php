<x-app-layout :metaTitle="$metaTitle">
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-10 text-white sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 text-sm text-emerald-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                <span class="mx-2">/</span>
                @if($type === 'course')
                    <a href="{{ route('courses.index') }}" class="transition hover:text-white">Courses</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Checkout</span>
                @else
                    <a href="{{ route('shop.index') }}" class="transition hover:text-white">Shop</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Checkout</span>
                @endif
            </nav>
            <h1 class="text-2xl font-extrabold sm:text-3xl">Checkout</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-emerald-100">
                {{ $checkoutIntro ?? ('Pay for your '.strtolower((string) $typeLabel).' with bKash or Nagad, then our team verifies your payment and confirms your order.') }}
            </p>
        </div>
    </section>

    <section class="bg-white py-10 sm:py-16" x-data="checkoutForm()">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="mb-2 font-bold">দয়া করে নিচের সমস্যাগুলো ঠিক করুন:</p>
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('checkout.unified.store', ['type' => $type, 'slug' => $purchasable->slug]) }}" @submit="validateForm">
                @csrf

                {{-- Step 1: Order summary --}}
                <div class="mb-8">
                    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">1</span>
                        Order Summary
                    </h2>
                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 sm:p-6">
                        <div class="flex flex-wrap items-start gap-4">
                            @php
                                $image = $type === 'course' ? $purchasable->thumbnail : $purchasable->cover_image;
                            @endphp
                            @if($image)
                                <img src="{{ asset('storage/' . $image) }}" alt="{{ $purchasable->title }}" class="h-20 w-20 flex-none rounded-xl object-cover sm:h-24 sm:w-24">
                            @else
                                <div class="flex h-20 w-20 flex-none items-center justify-center rounded-xl bg-emerald-100 text-3xl sm:h-24 sm:w-24">{{ $type === 'course' ? '🎓' : '📘' }}</div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-900">{{ $purchasable->title }}</h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $typeLabel }}</p>
                                @if($type === 'course' && $purchasable->duration_months)
                                    <p class="mt-1 text-sm text-slate-500">{{ $purchasable->duration_months }} {{ $purchasable->duration_months == 1 ? 'month' : 'months' }}</p>
                                @endif
                                @if($type === 'product' && $purchasable->category)
                                    <p class="mt-1 text-sm text-slate-500">{{ $purchasable->category }}</p>
                                @endif
                            </div>
                            <div class="w-full text-right sm:w-auto">
                                <span class="text-2xl font-extrabold text-[#0F5132]">৳{{ $displayPrice }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Buyer details --}}
                <div class="mb-8">
                    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">2</span>
                        Your Details
                    </h2>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="student_name" class="mb-1 block text-sm font-bold text-slate-700">Full Name</label>
                            <input type="text" id="student_name" name="student_name" value="{{ old('student_name', auth()->user()?->name) }}" required
                                   placeholder="Your full name" aria-required="true"
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 {{ $errors->has('student_name') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-emerald-200' }}">
                            @error('student_name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_email" class="mb-1 block text-sm font-bold text-slate-700">Email Address</label>
                            <input type="email" id="student_email" name="student_email" value="{{ old('student_email', auth()->user()?->email) }}" required
                                   placeholder="you@example.com" aria-required="true"
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 {{ $errors->has('student_email') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-emerald-200' }}">
                            @error('student_email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $type === 'course'
                                    ? 'Your course account is created or found with this email.'
                                    : 'Your download link is sent to this email.' }}
                            </p>
                        </div>
                        <div>
                            <label for="student_phone" class="mb-1 block text-sm font-bold text-slate-700">Mobile / WhatsApp Number</label>
                            <input type="tel" id="student_phone" name="student_phone" value="{{ old('student_phone', auth()->user()?->phone) }}" required
                                   placeholder="01XXXXXXXXX" aria-required="true"
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 {{ $errors->has('student_phone') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-emerald-200' }}">
                            @error('student_phone')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    </div>
                </div>

                {{-- Step 3: Payment method --}}
                <div class="mb-8">
                    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">3</span>
                        Payment Method
                    </h2>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <button type="button" @click="selectMethod('bkash')" :aria-pressed="method === 'bkash' ? 'true' : 'false'" aria-label="Pay with bKash"
                                :class="method === 'bkash' ? 'border-[#0F5132] bg-emerald-50 ring-2 ring-[#0F5132]' : 'border-slate-200 bg-white hover:border-emerald-300'"
                                class="flex min-h-[80px] items-center justify-center gap-3 rounded-2xl border-2 p-4 transition sm:min-h-[100px]">
                            <span class="text-xl font-extrabold text-[#D1206B] sm:text-2xl">bKash</span>
                        </button>
                        <button type="button" @click="selectMethod('nagad')" :aria-pressed="method === 'nagad' ? 'true' : 'false'" aria-label="Pay with Nagad"
                                :class="method === 'nagad' ? 'border-[#0F5132] bg-emerald-50 ring-2 ring-[#0F5132]' : 'border-slate-200 bg-white hover:border-emerald-300'"
                                class="flex min-h-[80px] items-center justify-center gap-3 rounded-2xl border-2 p-4 transition sm:min-h-[100px]">
                            <span class="text-xl font-extrabold text-[#E41E26] sm:text-2xl">Nagad</span>
                        </button>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-red-600" x-show="fieldErrors.method" x-text="fieldErrors.method"></p>
                    @error('payment_method')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    <input type="hidden" name="payment_method" x-bind:value="method" value="{{ old('payment_method') }}">
                </div>

                {{-- Step 4: Payment instructions + details --}}
                <div class="mb-8" x-show="method" x-transition>
                    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">4</span>
                        Payment
                    </h2>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-6">
                        <div class="mb-4 rounded-xl bg-white p-4 ring-1 ring-slate-200">
                            <p class="text-sm text-slate-500">Send <strong>৳{{ $displayPrice }}</strong> to this number</p>
                            <p class="mt-1 text-2xl font-extrabold tracking-wide text-slate-900" x-text="paymentNumber()"></p>
                        </div>
                        <ol class="mb-6 space-y-3 text-sm text-slate-600">
                            <li class="flex gap-3"><span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">1</span><span>Open your <strong x-text="methodLabel()"></strong> app</span></li>
                            <li class="flex gap-3"><span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">2</span><span>Send the exact amount to the number above</span></li>
                            <li class="flex gap-3"><span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">3</span><span>Copy the <strong>Transaction ID</strong> from your app</span></li>
                        </ol>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="transaction_id" class="mb-1 block text-sm font-bold text-slate-700">Transaction ID</label>
                                <input type="text" id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" required
                                       placeholder="Enter the Transaction ID from your app" aria-required="true"
                                       class="w-full break-words rounded-xl border px-4 py-3 text-sm focus:ring-2 {{ $errors->has('transaction_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-emerald-200' }}">
                                @error('transaction_id')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="sender_number" class="mb-1 block text-sm font-bold text-slate-700">Sender Mobile Number</label>
                                <input type="tel" id="sender_number" name="sender_number" value="{{ old('sender_number') }}" required
                                       placeholder="01XXXXXXXXX" aria-required="true"
                                       class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 {{ $errors->has('sender_number') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-emerald-200' }}">
                                @error('sender_number')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                                <p class="mt-1 text-xs text-slate-400">The mobile number you paid from.</p>
                            </div>
                        </div>

                        {{-- Final amount --}}
                        <div class="mt-6 flex items-center justify-between rounded-xl bg-white p-4 ring-1 ring-emerald-200">
                            <span class="text-sm font-bold text-slate-700">Amount to pay</span>
                            <span class="text-xl font-extrabold text-[#0F5132]">৳{{ $displayPrice }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="submitting"
                        :class="submitting ? 'cursor-wait opacity-70' : 'hover:bg-[#0d452c] active:scale-[0.98]'"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-[#0F5132] px-6 py-4 text-base font-bold text-white transition disabled:cursor-wait">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ $type === 'course' ? 'Confirm Payment & Enroll' : 'Confirm Payment & Submit for Approval' }}
                </button>

                <p class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-xs leading-relaxed text-emerald-800">
                    After you submit, our team verifies your payment.
                    {{ $type === 'course'
                        ? 'Your course is unlocked as soon as your payment is approved.'
                        : 'Your download link is sent by email as soon as your payment is approved.' }}
                    You will receive a confirmation email for this order.
                </p>
            </form>

            <div class="border-t border-slate-200 pt-6 text-center">
                <p class="text-sm text-slate-500">
                    Need help?
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                       class="font-bold text-[#25D366] transition hover:underline">
                        WhatsApp: +{{ $whatsappNumber }}
                    </a>
                </p>
                <p class="mt-2 text-xs text-slate-400">🔒 Secure checkout. Your information is safe with us.</p>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function checkoutForm() {
            return {
                method: '{{ old('payment_method', '') }}',
                submitting: false,
                fieldErrors: {},
                selectMethod(value) {
                    this.method = value;
                    this.fieldErrors.method = '';
                },
                methodLabel() {
                    return this.method === 'bkash' ? 'bKash' : 'Nagad';
                },
                paymentNumber() {
                    return this.method === 'bkash' ? '{{ $bkashNumber }}' : '{{ $nagadNumber }}';
                },
                validateForm(event) {
                    this.fieldErrors = {};
                    let valid = true;

                    if (!this.method) {
                        this.fieldErrors.method = 'Please select a payment method.';
                        valid = false;
                    }

                    if (!valid) {
                        event.preventDefault();
                        return;
                    }

                    this.submitting = true;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
