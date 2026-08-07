<x-app-layout :metaTitle="$course->title . ' | Checkout'">
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-10 text-white sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 text-sm text-emerald-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('courses.index') }}" class="transition hover:text-white">Courses</a>
                <span class="mx-2">/</span>
                <a href="{{ route('courses.show', $course->slug) }}" class="transition hover:text-white">{{ $course->title }}</a>
                <span class="mx-2">/</span>
                <span class="text-white">Checkout</span>
            </nav>
            <h1 class="text-2xl font-extrabold sm:text-3xl">Complete Your Enrollment</h1>
        </div>
    </section>

    <section class="bg-white py-10 sm:py-16" x-data="checkoutForm()">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            {{-- Validation Errors --}}
            <div x-show="errors.length > 0" x-cloak class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <template x-for="error in errors" :key="error">
                    <p x-text="error" class="mb-1 last:mb-0"></p>
                </template>
            </div>

            {{-- Step 1: Order Summary --}}
            <div class="mb-8">
                <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">1</span>
                    Order Summary
                </h2>
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        @if($course->thumbnail)
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" class="h-20 w-20 flex-none rounded-xl object-cover sm:h-24 sm:w-24">
                        @endif
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-900">{{ $course->title }}</h3>
                            @if($course->duration_weeks)
                                <p class="mt-1 text-sm text-slate-500">{{ $course->duration_weeks }} weeks</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold text-[#0F5132]">
                                @if($course->price > 0)
                                    ৳{{ number_format($course->price) }}
                                @else
                                    Free
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Select Payment Method --}}
            <div class="mb-8">
                <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">2</span>
                    Select Payment Method
                </h2>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <button type="button"
                        @click="selectMethod('bkash')"
                        :class="method === 'bkash'
                            ? 'border-[#0F5132] bg-emerald-50 ring-2 ring-[#0F5132]'
                            : 'border-slate-200 bg-white hover:border-emerald-300'"
                        class="flex min-h-[80px] items-center justify-center gap-3 rounded-2xl border-2 p-4 transition sm:min-h-[100px] sm:p-5">
                        <span class="text-xl font-extrabold text-[#D1206B] sm:text-2xl">bKash</span>
                    </button>
                    <button type="button"
                        @click="selectMethod('nagad')"
                        :class="method === 'nagad'
                            ? 'border-[#0F5132] bg-emerald-50 ring-2 ring-[#0F5132]'
                            : 'border-slate-200 bg-white hover:border-emerald-300'"
                        class="flex min-h-[80px] items-center justify-center gap-3 rounded-2xl border-2 p-4 transition sm:min-h-[100px] sm:p-5">
                        <span class="text-xl font-extrabold text-[#E41E26] sm:text-2xl">Nagad</span>
                    </button>
                </div>
            </div>

            {{-- Step 3: Payment Instructions --}}
            <div class="mb-8" x-show="method" x-transition>
                <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">3</span>
                    Payment Instructions
                </h2>
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-6">
                    <div class="mb-4 rounded-xl bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-500">Send payment to this number</p>
                        <p class="mt-1 text-2xl font-extrabold tracking-wide text-slate-900" x-text="paymentNumber"></p>
                        <p class="mt-1 text-xs text-slate-400" x-text="'(' + methodLabel + ')'"></p>
                    </div>
                    <ol class="space-y-3 text-sm text-slate-600">
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">1</span>
                            <span>Open your <strong x-text="methodLabel"></strong> app</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">2</span>
                            <span>Send <strong>৳{{ number_format($course->price) }}</strong> to the number above</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-[#0F5132] text-xs font-bold text-white">3</span>
                            <span>Save the <strong>Transaction ID</strong> from your app</span>
                        </li>
                    </ol>
                </div>
            </div>

            {{-- Step 4: Enter Payment Details --}}
            <div class="mb-8" x-show="method" x-transition>
                <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0F5132] text-sm font-bold text-white">4</span>
                    Enter Payment Details
                </h2>
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <form method="POST" action="{{ route('courses.enroll', $course->slug) }}" @submit="validateForm">
                        @csrf
                        <input type="hidden" name="payment_method" x-bind:value="method">

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-bold text-slate-700">Payment Method</label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700" x-text="methodLabel"></div>
                        </div>

                        <div class="mb-4">
                            <label for="transaction_id" class="mb-1 block text-sm font-bold text-slate-700">Transaction ID</label>
                            <input type="text" id="transaction_id" name="transaction_id"
                                x-model="transactionId"
                                @input="clearFieldError('transaction_id')"
                                :class="fieldErrors.transaction_id ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-[#0F5132]'"
                                placeholder="Enter the Transaction ID from your app"
                                class="w-full rounded-xl py-3 text-sm focus:ring-2"
                                required>
                            <p x-show="fieldErrors.transaction_id" x-text="fieldErrors.transaction_id" class="mt-1 text-xs text-red-500"></p>
                        </div>

                        <div class="mb-6">
                            <label for="sender_number" class="mb-1 block text-sm font-bold text-slate-700">Sender Mobile Number</label>
                            <input type="text" id="sender_number" name="sender_number"
                                x-model="senderNumber"
                                @input="clearFieldError('sender_number')"
                                :class="fieldErrors.sender_number ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#0F5132] focus:ring-[#0F5132]'"
                                placeholder="01XXXXXXXXX"
                                class="w-full rounded-xl py-3 text-sm focus:ring-2"
                                required>
                            <p x-show="fieldErrors.sender_number" x-text="fieldErrors.sender_number" class="mt-1 text-xs text-red-500"></p>
                        </div>

                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-full bg-[#0F5132] px-6 py-4 text-base font-bold text-white transition hover:bg-[#0d452c] active:scale-[0.98]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Confirm Payment & Enroll
                        </button>
                    </form>
                </div>
            </div>

            {{-- Footer help section --}}
            <div class="border-t border-slate-200 pt-6 text-center">
                <p class="text-sm text-slate-500">
                    Need help?
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                        class="font-bold text-[#25D366] transition hover:underline">
                        WhatsApp: +{{ $whatsappNumber }}
                    </a>
                </p>
                <p class="mt-2 text-xs text-slate-400">
                    🔒 Secure checkout. Your information is safe with us.
                </p>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function checkoutForm() {
            return {
                method: '',
                transactionId: '',
                senderNumber: '',
                errors: [],
                fieldErrors: {},

                get paymentNumber() {
                    const numbers = {
                        bkash: '{{ $bkashNumber }}',
                        nagad: '{{ $nagadNumber }}',
                    };
                    return numbers[this.method] || '';
                },

                get methodLabel() {
                    return this.method === 'bkash' ? 'bKash' : 'Nagad';
                },

                selectMethod(m) {
                    this.method = m;
                    this.errors = [];
                    this.fieldErrors = {};
                },

                clearFieldError(field) {
                    delete this.fieldErrors[field];
                    this.errors = [];
                },

                validateForm(e) {
                    this.errors = [];
                    this.fieldErrors = {};

                    if (!this.method) {
                        e.preventDefault();
                        this.errors.push('Please select a payment method.');
                        return;
                    }

                    if (!this.transactionId || this.transactionId.trim().length < 8) {
                        e.preventDefault();
                        this.fieldErrors.transaction_id = 'Transaction ID must be at least 8 characters.';
                        return;
                    }

                    const phonePattern = /^(?:\+88|88)?(01[3-9]\d{8})$/;
                    if (!this.senderNumber || !phonePattern.test(this.senderNumber.trim())) {
                        e.preventDefault();
                        this.fieldErrors.sender_number = 'Please enter a valid Bangladeshi mobile number (e.g. 01XXXXXXXXX).';
                        return;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
