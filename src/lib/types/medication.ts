export interface Medication {
    name: string;
    dosage: string;
    frequency: string; // "twice daily", "every 8 hours", etc.
    duration: string; // "7 days", "2 weeks", etc.
    quantity: number;
    instructions: string;
    warnings?: string[];
}
