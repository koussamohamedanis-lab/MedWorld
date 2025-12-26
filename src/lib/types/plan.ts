export const enum PlanID {
  premium = 'premium',
  standard = 'standard',
  basic = 'basic'
}

export type Plan = {
  id: number;
  planID: PlanID;
  name: string;
}
export const plans: Record<PlanID, Plan> = {
  [PlanID.basic]: {
    planID: PlanID.basic,
    name: 'Basic Plan',
    id: 0,
  },

  [PlanID.standard]: {
    planID: PlanID.standard,
    name: 'Standard Plan',
    id: 1,
  },

  [PlanID.premium]: {
    planID: PlanID.premium,
    name: 'Premium Plan',
    id: 2,
  },
};
