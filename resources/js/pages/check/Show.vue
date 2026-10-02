<script setup lang="ts">
import { Head, Link, router, useHttp, usePoll } from '@inertiajs/vue3';
import {
    BadgeCheck,
    CircleCheck,
    CircleHelp,
    ListChecks,
    ShieldAlert,
    ShieldX,
    TriangleAlert,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SourceLink from '@/components/SourceLink.vue';
import { Skeleton } from '@/components/ui/skeleton';
import checks from '@/routes/checks';
import type { Check, Verdict } from '@/types/check';

const props = defineProps<{ check: Check }>();

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
            no_red_flags_found: 'No red flags found',
            not_a_job_offer: 'Not a job offer',
        },
        severity: { high: 'High', medium: 'Medium', low: 'Low' },
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
            no_red_flags_found: 'لا توجد علامات تحذير',
            not_a_job_offer: 'ليس عرض عمل',
        },
        severity: { high: 'عالية', medium: 'متوسطة', low: 'منخفضة' },
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
    no_red_flags_found: {
        icon: CircleCheck,
        class: 'border-emerald-400/60 bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
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

const loading = computed(() =>
    ['pending', 'generating'].includes(props.check.status),
);

// Kick off the check, then poll as a fallback (e.g. page refreshed mid-check).
const http = useHttp(checks.run(props.check.id), {});
onMounted(() => {
    if (props.check.status === 'pending') {
        http.submit().finally(() => router.reload({ only: ['check'] }));
    }
});
const { stop } = usePoll(
    3000,
    { only: ['check'] },
    { autoStart: loading.value },
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

watch(
    loading,
    (isLoading) => {
        if (!isLoading) {
            stop();
            clearInterval(ticker);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head :title="c?.headline ?? t.yourMessage" />

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
            <Link
                :href="checks.create({ query: { lang: check.locale } })"
                class="mt-3 inline-block font-medium text-brand underline underline-offset-4"
                >{{ t.retry }}</Link
            >
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
        </article>
    </div>
</template>
