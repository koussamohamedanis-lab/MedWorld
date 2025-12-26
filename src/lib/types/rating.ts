import type { Cabinet } from "./cabinet";
import type { Patient } from "./users/patient";

// Si il a l'equippement + Si il sait l'utiliser.
export type EquippementRating = {
    disponibility: boolean;
    rating: number;
}

export type UserExperience = {
    reception: number;
    hygiene: number;
    waitTime: number;
    communication: number;
    professionalism: number;
    emplacement: number;
}

export type Rating = {
    id: number;

    // Entre:
    patient: Patient;
    cabinet: Cabinet;

    date: Date;

    // Equippement
    equippement: EquippementRating;

    // Acceuil dans le cabinet
    userExperience: UserExperience;

    review?: string;

}

export type Weights = {
    equippementWeight: number;
    userExperienceWeights: {
        reception: number;
        hygiene: number;
        waitTime: number;
        communication: number;
        professionalism: number;
        emplacement: number;
    }
};

export const DEFAULT_WEIGHTS: Weights = {
    equippementWeight: 0.2,
    userExperienceWeights: {
        reception: 0.2,
        hygiene: 0.5,
        waitTime: 0.2,
        communication: 0.2,
        professionalism: 0.2,
        emplacement: 0.2,
    },
};

export function calculateRatingScore(cabinet: Cabinet, weights: Weights = DEFAULT_WEIGHTS): number {

    if (!cabinet || !cabinet.ratings) return 0;

    let totalScore = 0;
    let totalWeight = 0;
    for (const rating of cabinet.ratings) {
        let ratingScore = 0;
        let ratingWeight = 0;
        // Equippement
        if (rating.equippement.disponibility) {
            ratingScore += rating.equippement.rating * weights.equippementWeight;
            ratingWeight += weights.equippementWeight;
        }
        // User Experience
        ratingScore += rating.userExperience.reception * weights.userExperienceWeights.reception;
        ratingWeight += weights.userExperienceWeights.reception;
        ratingScore += rating.userExperience.hygiene * weights.userExperienceWeights.hygiene;
        ratingWeight += weights.userExperienceWeights.hygiene;
        ratingScore += rating.userExperience.waitTime * weights.userExperienceWeights.waitTime;
        ratingWeight += weights.userExperienceWeights.waitTime;
        ratingScore += rating.userExperience.communication * weights.userExperienceWeights.communication;
        ratingWeight += weights.userExperienceWeights.communication;
        ratingScore += rating.userExperience.professionalism * weights.userExperienceWeights.professionalism;
        ratingWeight += weights.userExperienceWeights.professionalism;
        ratingScore += rating.userExperience.emplacement * weights.userExperienceWeights.emplacement;
        ratingWeight += weights.userExperienceWeights.emplacement;
        totalScore += ratingScore / ratingWeight;
        totalWeight += 1;
    }
    return totalScore / totalWeight;
}
