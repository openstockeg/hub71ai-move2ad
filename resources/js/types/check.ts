import type { Source } from '@/types/brief';

export type Verdict =
    | 'likely_scam'
    | 'suspicious'
    | 'no_red_flags_found'
    | 'not_a_job_offer';

export type CheckContent = {
    verdict: Verdict;
    headline: string;
    summary: string;
    red_flags: {
        title: string;
        severity: 'high' | 'medium' | 'low';
        quote: string | null;
        explanation: string;
        source_url: string | null;
    }[];
    good_signs: string[];
    next_steps: { title: string; detail: string; source_url: string | null }[];
};

export type Check = {
    id: string;
    message: string;
    locale: 'en' | 'ar';
    status: 'pending' | 'generating' | 'ready' | 'failed';
    content: CheckContent | null;
    sources: Source[];
    created_at: string;
};
