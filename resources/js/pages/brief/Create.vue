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
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/briefs';
import checks from '@/routes/checks';

// Autofocus only on desktop: on phones it pops the keyboard and suggestion list over the hero.
const isDesktop = window.matchMedia('(min-width: 768px)').matches;

const professions = [
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
];

const countries = [
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
];

const families = [
    { value: 'single', label: 'Just me' },
    { value: 'couple', label: 'Couple' },
    { value: 'family', label: 'Family' },
];

const locales = [
    { value: 'en', label: 'English' },
    { value: 'ar', label: 'العربية' },
];

const promises = [
    {
        icon: BadgeCheck,
        text: 'Answers link to official UAE and Abu Dhabi sources',
    },
    { icon: Sparkles, text: 'Personal to your profession, country and family' },
    { icon: ShieldAlert, text: 'Flags scams before they cost you money' },
];

// Same five stages the brief's "Your path" uses.
const journey = [
    {
        icon: Compass,
        stage: 'Explore',
        title: 'Do I fit?',
        text: 'Visa routes that match your profession, rated likely to unlikely.',
    },
    {
        icon: Plane,
        stage: 'Visit',
        title: 'Should I fly in first?',
        text: 'When a job-seeker visa helps, and when it just costs you.',
    },
    {
        icon: Luggage,
        stage: 'Move',
        title: 'Is this offer real?',
        text: 'Paste any offer, get every red flag with the law behind it.',
        href: checks.create(),
    },
    {
        icon: Home,
        stage: 'Settle',
        title: 'What will life cost?',
        text: 'Rent by area, upfront deposits and how to rent safely.',
    },
    {
        icon: Rocket,
        stage: 'Build',
        title: 'How do I stay long-term?',
        text: 'Golden and Green Visa thresholds once you are here.',
    },
];
</script>

<template>
    <Head title="Your move to Abu Dhabi, answered" />

    <div>
        <section class="bg-sand">
            <!-- Mobile order: intro → form → promises. Desktop: intro + promises left, form right. -->
            <div
                class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-4 py-6 md:grid-cols-[1fr_420px] md:gap-x-10 md:gap-y-8 md:py-20"
            >
                <div class="flex flex-col justify-end">
                    <p
                        class="mb-2 text-xs font-medium tracking-wide text-brand uppercase md:mb-3 md:text-sm"
                    >
                        Thinking about Abu Dhabi?
                    </p>
                    <h1
                        class="text-3xl leading-tight font-semibold tracking-tight md:text-5xl"
                    >
                        The trusted answer to "can I move to Abu Dhabi?"
                    </h1>
                    <p
                        class="mt-3 max-w-lg text-base text-muted-foreground md:mt-4 md:text-lg"
                    >
                        Tell us who you are. In under a minute you get a
                        personal brief: which visa fits you, what life costs,
                        the exact steps — and the scams to avoid. Sourced, not
                        guessed.
                    </p>
                </div>

                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="grid gap-5 rounded-xl border bg-card p-5 shadow-sm md:col-start-2 md:row-span-2 md:row-start-1 md:self-center md:p-6"
                >
                    <h2 class="text-lg font-semibold">
                        Get your Abu Dhabi brief
                    </h2>

                    <div class="grid gap-2">
                        <Label for="profession">Your profession</Label>
                        <Input
                            id="profession"
                            name="profession"
                            list="professions"
                            autocomplete="off"
                            required
                            v-focus="isDesktop"
                            placeholder="e.g. Software engineer, nurse"
                        />
                        <datalist id="professions">
                            <option
                                v-for="p in professions"
                                :key="p"
                                :value="p"
                            />
                        </datalist>
                        <InputError :message="errors.profession" />
                    </div>

                    <div class="grid grid-cols-[1fr_110px] gap-3">
                        <div class="grid gap-2">
                            <Label for="country">Where you live now</Label>
                            <Input
                                id="country"
                                name="country"
                                list="countries"
                                autocomplete="off"
                                required
                                placeholder="e.g. Egypt"
                            />
                            <datalist id="countries">
                                <option
                                    v-for="c in countries"
                                    :key="c"
                                    :value="c"
                                />
                            </datalist>
                            <InputError :message="errors.country" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="experience_years">Years exp.</Label>
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
                            Who is moving?
                        </legend>
                        <div class="grid grid-cols-3 gap-2">
                            <label
                                v-for="(option, i) in families"
                                :key="option.value"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="family"
                                    :value="option.value"
                                    :checked="i === 0"
                                    class="sr-only"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                        <InputError :message="errors.family" />
                    </fieldset>

                    <fieldset class="grid gap-2">
                        <legend class="mb-2 text-sm font-medium">
                            Brief language
                        </legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                v-for="(option, i) in locales"
                                :key="option.value"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="locale"
                                    :value="option.value"
                                    :checked="i === 0"
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
                        Build my brief
                    </Button>
                    <p class="text-center text-xs text-muted-foreground">
                        Free · no account needed
                    </p>
                </Form>

                <ul class="grid gap-3 self-start">
                    <li
                        v-for="promise in promises"
                        :key="promise.text"
                        class="flex items-center gap-3 text-sm"
                    >
                        <component
                            :is="promise.icon"
                            class="size-5 shrink-0 text-brand"
                        />
                        {{ promise.text }}
                    </li>
                    <li>
                        <Link
                            :href="checks.create()"
                            class="mt-1 inline-flex items-center gap-2 rounded-lg border border-destructive/30 bg-card px-3 py-2 text-sm font-medium hover:border-destructive/60"
                        >
                            <ShieldAlert class="size-4 text-destructive" />
                            Already have a job offer? Check it for scams →
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <section class="mx-auto max-w-5xl px-4 py-10 md:py-14">
            <p
                class="text-xs font-medium tracking-wide text-brand uppercase md:text-sm"
            >
                One brief, the whole move
            </p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">
                Every step, answered from official sources
            </h2>
            <ol class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <li v-for="(step, i) in journey" :key="step.stage">
                    <component
                        :is="step.href ? Link : 'div'"
                        :href="step.href"
                        class="flex h-full flex-col gap-2 rounded-xl border bg-card p-4"
                        :class="step.href && 'transition hover:border-brand'"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex size-8 items-center justify-center rounded-full bg-brand/10 text-brand"
                            >
                                <component :is="step.icon" class="size-4" />
                            </span>
                            <span
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >{{ i + 1 }} · {{ step.stage }}</span
                            >
                        </div>
                        <p class="font-medium">{{ step.title }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ step.text }}
                        </p>
                        <span
                            v-if="step.href"
                            class="mt-auto text-sm font-medium text-brand"
                            >Check an offer →</span
                        >
                    </component>
                </li>
            </ol>
        </section>
    </div>
</template>
