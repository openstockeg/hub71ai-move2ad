<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { BadgeCheck, ShieldAlert, Sparkles } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/briefs';

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
    { value: 'couple', label: 'With partner' },
    { value: 'family', label: 'With kids' },
];

const locales = [
    { value: 'en', label: 'English' },
    { value: 'ar', label: 'العربية' },
];

const promises = [
    {
        icon: BadgeCheck,
        text: 'Every answer links to an official .gov.ae source',
    },
    { icon: Sparkles, text: 'Personal to your profession, country and family' },
    { icon: ShieldAlert, text: 'Flags scams before they cost you money' },
];
</script>

<template>
    <Head title="Your move to Abu Dhabi, answered" />

    <section class="bg-sand">
        <div
            class="mx-auto grid max-w-5xl gap-10 px-4 py-12 md:grid-cols-[1fr_420px] md:py-20"
        >
            <div class="flex flex-col justify-center">
                <p
                    class="mb-3 text-sm font-medium tracking-wide text-brand uppercase"
                >
                    Thinking about Abu Dhabi?
                </p>
                <h1
                    class="text-4xl leading-tight font-semibold tracking-tight md:text-5xl"
                >
                    The trusted answer to "can I move to Abu Dhabi?"
                </h1>
                <p class="mt-4 max-w-lg text-lg text-muted-foreground">
                    Tell us who you are. In under a minute you get a personal
                    brief: which visa fits you, what life costs, the exact
                    steps — and the scams to avoid. Sourced, not guessed.
                </p>
                <ul class="mt-8 grid gap-3">
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
                </ul>
            </div>

            <Form
                v-bind="store.form()"
                v-slot="{ errors, processing }"
                class="grid gap-5 rounded-xl border bg-card p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold">Get your Abu Dhabi brief</h2>

                <div class="grid gap-2">
                    <Label for="profession">Your profession</Label>
                    <Input
                        id="profession"
                        name="profession"
                        list="professions"
                        autocomplete="off"
                        required
                        v-focus
                        placeholder="e.g. Software engineer, nurse, teacher"
                    />
                    <datalist id="professions">
                        <option v-for="p in professions" :key="p" :value="p" />
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
                            <option v-for="c in countries" :key="c" :value="c" />
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
        </div>
    </section>
</template>
