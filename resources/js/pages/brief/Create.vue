<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Compass,
    Home,
    Luggage,
    Plane,
    Rocket,
    ShieldAlert,
    Sparkles,
} from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';
import { store } from '@/routes/briefs';
import checks from '@/routes/checks';

const props = defineProps<{ locale: 'en' | 'ar' }>();

// Autofocus only on desktop: on phones it pops the keyboard and suggestion list over the hero.
const isDesktop = window.matchMedia('(min-width: 768px)').matches;

const labels = {
    en: {
        pageTitle: 'Your move to Abu Dhabi, answered',
        title: 'Can I move to Abu Dhabi?',
        intro: 'Get the trusted answer for you. Tell us who you are and in under a minute you get a personal brief: which visa fits, what life costs, the exact steps, and the scams to avoid. Every claim links to its official source.',
        formTitle: 'Get your Abu Dhabi brief',
        profession: 'Your profession',
        professionPlaceholder: 'e.g. Software engineer, nurse',
        country: 'Where you live now',
        countryPlaceholder: 'e.g. Egypt',
        years: 'Years exp.',
        who: 'Who is moving?',
        families: { single: 'Just me', couple: 'Couple', family: 'Family' },
        language: 'Brief language',
        submit: 'Build my brief',
        free: 'Free, no account needed',
        promises: [
            'Answers link to official UAE and Abu Dhabi sources',
            'Personal to your profession, country and family',
            'Flags scams before they cost you money',
        ],
        checkCta: 'Already have a job offer? Check it for scams',
        journeyTitle:
            'One brief covers the whole move, from first question to staying for good',
        journey: [
            {
                stage: 'Explore',
                title: 'Do I fit?',
                text: 'Visa routes that match your profession, rated likely to unlikely.',
            },
            {
                stage: 'Visit',
                title: 'Should I fly in first?',
                text: 'When a job-seeker visa helps, and when it just costs you.',
            },
            {
                stage: 'Move',
                title: 'Is this offer real?',
                text: 'Paste any offer, get every red flag with the law behind it.',
            },
            {
                stage: 'Settle',
                title: 'What will life cost?',
                text: 'Rent by area, upfront deposits and how to rent safely.',
            },
            {
                stage: 'Build',
                title: 'How do I stay long-term?',
                text: 'Golden and Green Visa thresholds once you are here.',
            },
        ],
        checkLink: 'Check a job offer',
        professions: [
            'Software engineer',
            'Data scientist',
            'AI / ML engineer',
            'Registered nurse',
            'Doctor',
            'Teacher',
            'Civil engineer',
            'Electrical engineer',
            'Accountant',
            'Financial analyst',
            'Marketing manager',
            'Product manager',
            'Architect',
            'Pharmacist',
            'Startup founder',
        ],
        countries: [
            'Egypt',
            'India',
            'Pakistan',
            'Philippines',
            'Jordan',
            'Lebanon',
            'Morocco',
            'Tunisia',
            'Nigeria',
            'Kenya',
            'United Kingdom',
            'United States',
            'Turkey',
            'Brazil',
        ],
    },
    ar: {
        pageTitle: 'انتقالك إلى أبوظبي، بإجابات موثوقة',
        title: 'هل يمكنني الانتقال إلى أبوظبي؟',
        intro: 'احصل على الإجابة الموثوقة لحالتك. أخبرنا من أنت، وخلال أقل من دقيقة تحصل على ملخص شخصي: التأشيرة المناسبة، تكاليف المعيشة، الخطوات بالتفصيل، وعمليات الاحتيال التي يجب تجنبها. كل معلومة مرتبطة بمصدرها الرسمي.',
        formTitle: 'احصل على ملخصك عن أبوظبي',
        profession: 'مهنتك',
        professionPlaceholder: 'مثال: مهندس برمجيات، ممرض',
        country: 'بلد إقامتك الحالي',
        countryPlaceholder: 'مثال: مصر',
        years: 'سنوات الخبرة',
        who: 'من سينتقل؟',
        families: { single: 'أنا فقط', couple: 'زوجان', family: 'عائلة' },
        language: 'لغة الملخص',
        submit: 'أنشئ ملخصي',
        free: 'مجاني، بدون حساب',
        promises: [
            'الإجابات مرتبطة بمصادر رسمية في الإمارات وأبوظبي',
            'مخصصة لمهنتك وبلدك وعائلتك',
            'تنبهك إلى الاحتيال قبل أن يكلفك المال',
        ],
        checkCta: 'لديك عرض عمل؟ افحصه من الاحتيال',
        journeyTitle:
            'ملخص واحد يغطي الانتقال كله، من أول سؤال حتى الاستقرار الدائم',
        journey: [
            {
                stage: 'استكشف',
                title: 'هل أنا مناسب؟',
                text: 'مسارات التأشيرة المناسبة لمهنتك، مرتبة من المرجح إلى المستبعد.',
            },
            {
                stage: 'زُر',
                title: 'هل أسافر أولًا؟',
                text: 'متى تفيدك تأشيرة البحث عن عمل، ومتى تكلفك فقط.',
            },
            {
                stage: 'انتقل',
                title: 'هل هذا العرض حقيقي؟',
                text: 'الصق أي عرض، واحصل على كل علامة تحذير مع القانون الذي يستند إليها.',
            },
            {
                stage: 'استقر',
                title: 'كم ستكلف الحياة؟',
                text: 'الإيجار حسب المنطقة، والتأمينات المقدمة، وكيف تستأجر بأمان.',
            },
            {
                stage: 'ابنِ',
                title: 'كيف أبقى على المدى الطويل؟',
                text: 'شروط الإقامة الذهبية والخضراء بعد وصولك.',
            },
        ],
        checkLink: 'افحص عرض عمل',
        professions: [
            'مهندس برمجيات',
            'عالم بيانات',
            'مهندس ذكاء اصطناعي',
            'ممرض',
            'طبيب',
            'معلم',
            'مهندس مدني',
            'مهندس كهرباء',
            'محاسب',
            'محلل مالي',
            'مدير تسويق',
            'مدير منتج',
            'مهندس معماري',
            'صيدلي',
            'مؤسس شركة ناشئة',
        ],
        countries: [
            'مصر',
            'الأردن',
            'لبنان',
            'سوريا',
            'المغرب',
            'تونس',
            'الجزائر',
            'العراق',
            'السودان',
            'فلسطين',
            'اليمن',
            'الهند',
            'باكستان',
            'الفلبين',
        ],
    },
} as const;

const t = computed(() => labels[props.locale]);

const families = ['single', 'couple', 'family'] as const;

const locales = [
    { value: 'en', label: 'English' },
    { value: 'ar', label: 'العربية' },
] as const;

const promiseIcons = [BadgeCheck, Sparkles, ShieldAlert];

// Same five stages the brief's "Your path" uses; the third links to the scam check.
const journeyIcons = [Compass, Plane, Luggage, Home, Rocket];
const checkHref = computed(() =>
    checks.create({ query: { lang: props.locale } }),
);
</script>

<template>
    <Head :title="t.pageTitle" />

    <div>
        <section class="bg-sand">
            <!-- Mobile order: intro → form → promises. Desktop: intro + promises left, form right. -->
            <div
                class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-4 py-6 md:grid-cols-[1fr_420px] md:gap-x-10 md:gap-y-8 md:py-20"
            >
                <div class="flex flex-col justify-end">
                    <div
                        class="mb-5 flex w-fit gap-1 rounded-md border bg-card p-0.5 text-sm md:mb-8"
                    >
                        <Link
                            v-for="option in locales"
                            :key="option.value"
                            :href="home({ query: { lang: option.value } })"
                            replace
                            class="rounded px-2.5 py-1"
                            :class="
                                locale === option.value
                                    ? 'bg-brand/10 font-medium text-brand'
                                    : 'text-muted-foreground'
                            "
                            >{{ option.label }}</Link
                        >
                    </div>
                    <h1
                        class="text-4xl leading-[1.05] font-semibold tracking-[-0.03em] text-balance md:text-6xl"
                        :class="
                            locale === 'ar' && 'leading-[1.25] tracking-normal'
                        "
                    >
                        {{ t.title }}
                    </h1>
                    <p
                        class="mt-4 max-w-lg text-base text-pretty text-muted-foreground md:mt-6 md:text-lg"
                    >
                        {{ t.intro }}
                    </p>
                </div>

                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="grid gap-5 rounded-xl border bg-card p-5 shadow-sm md:col-start-2 md:row-span-2 md:row-start-1 md:self-center md:p-6"
                >
                    <h2 class="text-lg font-semibold">
                        {{ t.formTitle }}
                    </h2>

                    <div class="grid gap-2">
                        <Label for="profession">{{ t.profession }}</Label>
                        <Input
                            id="profession"
                            name="profession"
                            list="professions"
                            autocomplete="off"
                            required
                            v-focus="isDesktop"
                            :placeholder="t.professionPlaceholder"
                        />
                        <datalist id="professions">
                            <option
                                v-for="p in t.professions"
                                :key="p"
                                :value="p"
                            />
                        </datalist>
                        <InputError :message="errors.profession" />
                    </div>

                    <div class="grid grid-cols-[1fr_110px] gap-3">
                        <div class="grid gap-2">
                            <Label for="country">{{ t.country }}</Label>
                            <Input
                                id="country"
                                name="country"
                                list="countries"
                                autocomplete="off"
                                required
                                :placeholder="t.countryPlaceholder"
                            />
                            <datalist id="countries">
                                <option
                                    v-for="c in t.countries"
                                    :key="c"
                                    :value="c"
                                />
                            </datalist>
                            <InputError :message="errors.country" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="experience_years">{{ t.years }}</Label>
                            <Input
                                id="experience_years"
                                name="experience_years"
                                type="number"
                                min="0"
                                max="50"
                                required
                                default-value="5"
                            />
                            <InputError :message="errors.experience_years" />
                        </div>
                    </div>

                    <fieldset class="grid gap-2">
                        <legend class="mb-2 text-sm font-medium">
                            {{ t.who }}
                        </legend>
                        <div class="grid grid-cols-3 gap-2">
                            <label
                                v-for="(family, i) in families"
                                :key="family"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="family"
                                    :value="family"
                                    :checked="i === 0"
                                    class="sr-only"
                                />
                                {{ t.families[family] }}
                            </label>
                        </div>
                        <InputError :message="errors.family" />
                    </fieldset>

                    <fieldset class="grid gap-2">
                        <legend class="mb-2 text-sm font-medium">
                            {{ t.language }}
                        </legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                v-for="option in locales"
                                :key="option.value"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="locale"
                                    :value="option.value"
                                    :checked="option.value === locale"
                                    class="sr-only"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                    </fieldset>

                    <Button
                        type="submit"
                        size="lg"
                        class="bg-brand text-brand-foreground hover:bg-brand/90"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        {{ t.submit }}
                    </Button>
                    <p class="text-center text-xs text-muted-foreground">
                        {{ t.free }}
                    </p>
                </Form>

                <ul class="grid gap-3 self-start">
                    <li
                        v-for="(promise, i) in t.promises"
                        :key="promise"
                        class="flex items-center gap-3 text-sm"
                    >
                        <component
                            :is="promiseIcons[i]"
                            class="size-5 shrink-0 text-brand"
                        />
                        {{ promise }}
                    </li>
                    <li>
                        <Link
                            :href="checkHref"
                            class="mt-1 inline-flex items-center gap-2 rounded-lg border border-destructive/30 bg-card px-3 py-2 text-sm font-medium hover:border-destructive/60"
                        >
                            <ShieldAlert class="size-4 text-destructive" />
                            {{ t.checkCta }}
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <section class="mx-auto max-w-5xl px-4 py-12 md:py-16">
            <h2
                class="max-w-xl text-2xl font-semibold tracking-tight text-balance md:text-3xl"
            >
                {{ t.journeyTitle }}
            </h2>
            <!-- A connected path: vertical on phones, horizontal on desktop. -->
            <ol class="mt-8 grid gap-8 md:mt-10 lg:grid-cols-5 lg:gap-6">
                <li
                    v-for="(step, i) in t.journey"
                    :key="step.stage"
                    class="relative ps-12 before:absolute before:start-4 before:top-10 before:-bottom-6 before:w-0.5 before:bg-brand/25 last:before:hidden lg:ps-0 lg:pt-14 lg:before:start-11 lg:before:-end-4 lg:before:top-4 lg:before:bottom-auto lg:before:h-0.5 lg:before:w-auto"
                >
                    <span
                        class="absolute start-0 top-0 flex size-8 items-center justify-center rounded-full bg-brand text-brand-foreground"
                    >
                        <component :is="journeyIcons[i]" class="size-4" />
                    </span>
                    <p class="text-sm font-medium text-brand">
                        {{ step.stage }}
                    </p>
                    <p class="mt-1 font-semibold">{{ step.title }}</p>
                    <p class="mt-1 text-sm text-pretty text-muted-foreground">
                        {{ step.text }}
                    </p>
                    <Link
                        v-if="i === 2"
                        :href="checkHref"
                        class="mt-2 inline-block text-sm font-medium text-brand underline underline-offset-4 hover:no-underline"
                        >{{ t.checkLink }}</Link
                    >
                </li>
            </ol>
        </section>
    </div>
</template>
