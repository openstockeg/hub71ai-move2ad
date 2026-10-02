<script setup lang="ts">
import { Head, Link, router, useHttp, usePoll } from '@inertiajs/vue3';
import {
    BadgeCheck,
    CircleCheck,
    CircleHelp,
    Info,
    ListChecks,
    ShieldAlert,
    ShieldCheck,
    ShieldX,
    TriangleAlert,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SourceLink from '@/components/SourceLink.vue';
import { Skeleton } from '@/components/ui/skeleton';
import checks from '@/routes/checks';
import type { Check, Verdict } from '@/types/check';

const props = defineProps<{ check: Check }>();

// Official MOHRE service to look up a job offer by its number (also in facts.json).
const MOHRE_OFFER_INQUIRY = 'https://receipts.mohre.gov.ae/OfferInquiry/Index';

const labels = {
    en: {
        yourMessage: 'The offer you pasted',
        loading: 'Checking this offer against UAE law…',
        loadingSteps: [
            'Looking for requests for money',
            'Checking UAE labour law on recruitment fees',
            'Comparing with official scam warnings',
            'Writing your result',
        ],
        failed: 'We could not finish this check. Please try again.',
        retry: 'Try again',
        redFlags: 'Red flags',
        goodSigns: 'Reassuring signs',
        nextSteps: 'What to do now',
        another: 'Check another offer',
        verdict: {
            likely_scam: 'Likely scam',
            suspicious: 'Suspicious',
            no_red_flags_found: 'No obvious red flags — verify before you act',
            not_a_job_offer: 'Not a job offer',
        },
        severity: { high: 'High', medium: 'Medium', low: 'Low' },
        verifyTitle: 'Always verify before you act',
        verifyText:
            'We only read the text you pasted — we cannot see the employer, so this page is never proof that an offer is real. A genuine UAE job offer has an offer number you can look up on MOHRE. Never pay anyone to get a job.',
        verifyLink: 'Look up your offer on MOHRE',
    },
    ar: {
        yourMessage: 'العرض الذي لصقته',
        loading: 'نفحص هذا العرض وفق القانون الإماراتي…',
        loadingSteps: [
            'البحث عن طلبات دفع أموال',
            'مراجعة قانون العمل الإماراتي بشأن رسوم التوظيف',
            'المقارنة بالتحذيرات الرسمية من الاحتيال',
            'كتابة النتيجة',
        ],
        failed: 'تعذّر إكمال الفحص. يرجى المحاولة مرة أخرى.',
        retry: 'حاول مجددًا',
        redFlags: 'علامات التحذير',
        goodSigns: 'علامات مطمئنة',
        nextSteps: 'ماذا تفعل الآن',
        another: 'افحص عرضًا آخر',
        verdict: {
            likely_scam: 'احتيال على الأرجح',
            suspicious: 'مشبوه',
            no_red_flags_found: 'لا علامات تحذير واضحة — تحقّق قبل أي خطوة',
            not_a_job_offer: 'ليس عرض عمل',
        },
        severity: { high: 'عالية', medium: 'متوسطة', low: 'منخفضة' },
        verifyTitle: 'تحقّق دائمًا قبل أي خطوة',
        verifyText:
            'نحن نقرأ النص الذي لصقته فقط ولا نرى صاحب العمل، لذلك هذه الصفحة ليست دليلًا على أن العرض حقيقي. عرض العمل الحقيقي في الإمارات يحمل رقمًا يمكنك التحقق منه لدى وزارة الموارد البشرية والتوطين. لا تدفع لأي أحد مقابل الحصول على وظيفة.',
        verifyLink: 'تحقّق من عرضك لدى وزارة الموارد البشرية والتوطين',
    },
} as const;

const t = computed(() => labels[props.check.locale]);
const c = computed(() => props.check.content);
const dir = computed(() => (props.check.locale === 'ar' ? 'rtl' : 'ltr'));

const verdictStyle: Record<Verdict, { icon: typeof ShieldX; class: string }> = {
    likely_scam: {
        icon: ShieldX,
        class: 'border-destructive/50 bg-destructive/10 text-destructive',
    },
    suspicious: {
        icon: TriangleAlert,
        class: 'border-amber-400/60 bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    },
    // Deliberately not green: we only read the text, so this is never an approval.
    no_red_flags_found: {
        icon: Info,
        class: 'border-sky-400/60 bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
    },
    not_a_job_offer: {
        icon: CircleHelp,
        class: 'border-border bg-muted text-muted-foreground',
    },
};

const severityClass = {
    high: 'bg-destructive text-white',
    medium: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    low: 'bg-muted text-muted-foreground',
};

// Split the pasted message so the phrases quoted as red flags are highlighted in place.
const segments = computed(() => {
    const message = props.check.message;
    const ranges = (c.value?.red_flags ?? [])
        .map((flag) => flag.quote?.trim())
        .filter((quote): quote is string => !!quote)
        .map((quote) => {
            const start = message.toLowerCase().indexOf(quote.toLowerCase());

            return start === -1 ? null : [start, start + quote.length];
        })
        .filter((range): range is number[] => range !== null)
        .sort((a, b) => a[0] - b[0]);

    const parts: { text: string; flagged: boolean }[] = [];
    let cursor = 0;

    for (const [start, end] of ranges) {
        if (end <= cursor) {
            continue;
        }

        const from = Math.max(start, cursor);

        if (from > cursor) {
            parts.push({ text: message.slice(cursor, from), flagged: false });
        }

        parts.push({ text: message.slice(from, end), flagged: true });
        cursor = end;
    }

    parts.push({ text: message.slice(cursor), flagged: false });

    return parts;
});

const retrying = ref(false);
const loading = computed(
    () =>
        retrying.value ||
        ['pending', 'generating'].includes(props.check.status),
);

// Run the check, then poll as a fallback (e.g. edge timeout or page refreshed mid-check).
const http = useHttp(checks.run(props.check.id), {});
const run = () =>
    http
        .submit()
        .catch(() => {})
        .finally(() =>
            router.reload({
                only: ['check'],
                onFinish: () => (retrying.value = false),
            }),
        );
onMounted(() => {
    if (props.check.status === 'pending') {
        run();
    }
});

// Retry the same message in place, so the user never has to paste it again.
const retry = () => {
    retrying.value = true;
    loadingStep.value = 0;
    run();
};

const { start, stop } = usePoll(
    3000,
    { only: ['check'] },
    { autoStart: false },
);

// Rotate loading messages while waiting.
const loadingStep = ref(0);
const ticker = setInterval(() => {
    loadingStep.value = Math.min(
        loadingStep.value + 1,
        t.value.loadingSteps.length - 1,
    );
}, 5000);
onUnmounted(() => clearInterval(ticker));

watch(loading, (isLoading) => (isLoading ? start() : stop()), {
    immediate: true,
});
</script>

<template>
    <!-- Pasted messages can contain personal details: keep these pages out of search engines. -->
    <Head :title="c?.headline ?? t.yourMessage">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <!-- grid-cols-1 (minmax(0, 1fr)) everywhere: long words/URLs must never widen the page. -->
    <div
        :dir="dir"
        :lang="check.locale"
        class="mx-auto grid max-w-3xl grid-cols-1 gap-6 px-4 py-6 wrap-break-word md:gap-8 md:py-12"
    >
        <section>
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm">
                <h2 class="font-medium text-muted-foreground">
                    {{ t.yourMessage }}
                </h2>
                <Link
                    :href="checks.create({ query: { lang: check.locale } })"
                    class="ms-auto text-muted-foreground underline underline-offset-4 hover:text-foreground"
                    >{{ t.another }}</Link
                >
            </div>
            <blockquote
                dir="auto"
                class="max-h-56 overflow-y-auto rounded-xl border bg-sand p-4 text-sm whitespace-pre-line"
            >
                <template v-for="(part, i) in segments" :key="i">
                    <mark
                        v-if="part.flagged"
                        class="rounded bg-destructive/15 px-0.5 text-foreground underline decoration-destructive decoration-wavy underline-offset-4"
                        >{{ part.text }}</mark
                    >
                    <template v-else>{{ part.text }}</template>
                </template>
            </blockquote>
        </section>

        <!-- Pending -->
        <div v-if="loading" class="grid grid-cols-1 gap-6">
            <div class="rounded-xl border bg-card p-6">
                <p class="flex items-center gap-2 font-medium">
                    <span class="relative flex size-3">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-brand opacity-60"
                        />
                        <span
                            class="relative inline-flex size-3 rounded-full bg-brand"
                        />
                    </span>
                    {{ t.loading }}
                </p>
                <ul class="mt-4 grid gap-2 text-sm">
                    <li
                        v-for="(step, i) in t.loadingSteps"
                        :key="step"
                        class="flex items-center gap-2 transition-opacity"
                        :class="i <= loadingStep ? 'opacity-100' : 'opacity-30'"
                    >
                        <BadgeCheck
                            class="size-4"
                            :class="
                                i < loadingStep
                                    ? 'text-brand'
                                    : 'text-muted-foreground'
                            "
                        />
                        {{ step }}
                    </li>
                </ul>
            </div>
            <Skeleton class="h-24 w-full" />
            <Skeleton class="h-40 w-full" />
        </div>

        <!-- Failed -->
        <div
            v-else-if="check.status === 'failed' || !c"
            class="rounded-xl border border-destructive/40 bg-card p-6"
        >
            <p>{{ t.failed }}</p>
            <button
                type="button"
                class="mt-3 font-medium text-brand underline underline-offset-4"
                @click="retry"
            >
                {{ t.retry }}
            </button>
        </div>

        <!-- Ready -->
        <article v-else class="grid grid-cols-1 gap-8 md:gap-10">
            <header
                class="rounded-xl border-2 p-5"
                :class="verdictStyle[c.verdict].class"
            >
                <p class="flex items-center gap-2 text-lg font-bold">
                    <component
                        :is="verdictStyle[c.verdict].icon"
                        class="size-6 shrink-0"
                    />
                    {{ t.verdict[c.verdict] }}
                </p>
                <h1
                    class="mt-2 text-xl leading-snug font-semibold text-foreground md:text-2xl"
                >
                    {{ c.headline }}
                </h1>
                <p class="mt-2 text-sm text-foreground/80 md:text-base">
                    {{ c.summary }}
                </p>
            </header>

            <section v-if="c.red_flags.length">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <ShieldAlert class="size-5 text-destructive" />{{
                        t.redFlags
                    }}
                </h2>
                <div class="grid grid-cols-1 gap-3">
                    <div
                        v-for="flag in c.red_flags"
                        :key="flag.title"
                        class="rounded-xl border bg-card p-4"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="severityClass[flag.severity]"
                                >{{ t.severity[flag.severity] }}</span
                            >
                            <h3 class="font-semibold">{{ flag.title }}</h3>
                        </div>
                        <p
                            v-if="flag.quote"
                            dir="auto"
                            class="mt-2 border-s-2 border-destructive ps-3 text-sm italic"
                        >
                            “{{ flag.quote }}”
                        </p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ flag.explanation }}
                        </p>
                        <div class="mt-3">
                            <SourceLink
                                :url="flag.source_url"
                                :sources="check.sources"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="c.good_signs.length">
                <h2 class="mb-3 text-xl font-semibold">{{ t.goodSigns }}</h2>
                <ul class="grid grid-cols-1 gap-2 text-sm">
                    <li
                        v-for="sign in c.good_signs"
                        :key="sign"
                        class="flex items-start gap-2"
                    >
                        <CircleCheck
                            class="mt-0.5 size-4 shrink-0 text-emerald-600"
                        />
                        {{ sign }}
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <ListChecks class="size-5 text-brand" />{{ t.nextSteps }}
                </h2>
                <ol class="grid grid-cols-1 gap-3">
                    <li
                        v-for="(step, i) in c.next_steps"
                        :key="i"
                        class="flex gap-3 rounded-xl border bg-card p-4"
                    >
                        <span
                            class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-semibold text-brand-foreground"
                            >{{ i + 1 }}</span
                        >
                        <div class="min-w-0">
                            <h3 class="font-semibold">{{ step.title }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ step.detail }}
                            </p>
                            <div class="mt-2">
                                <SourceLink
                                    :url="step.source_url"
                                    :sources="check.sources"
                                />
                            </div>
                        </div>
                    </li>
                </ol>
            </section>

            <aside class="rounded-xl border bg-sand p-4 text-sm">
                <h2 class="flex items-center gap-2 font-semibold">
                    <ShieldCheck class="size-4 shrink-0 text-brand" />{{
                        t.verifyTitle
                    }}
                </h2>
                <p class="mt-1 text-muted-foreground">{{ t.verifyText }}</p>
                <a
                    :href="MOHRE_OFFER_INQUIRY"
                    target="_blank"
                    rel="noopener"
                    class="mt-3 inline-flex items-center gap-1.5 font-medium text-brand underline underline-offset-4"
                >
                    <BadgeCheck class="size-4 shrink-0" />{{ t.verifyLink }}
                </a>
            </aside>
        </article>
    </div>
</template>
