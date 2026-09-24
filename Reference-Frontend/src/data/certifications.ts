import type { Certification } from '@/types/content';

export const certifications: Certification[] = [
  {
    id: 'aws-sa-pro',
    name: 'AWS Certified Solutions Architect — Professional',
    issuer: 'Amazon Web Services',
    year: 2024,
    credentialId: 'AWS-PSA-88213',
    url: 'https://aws.amazon.com/certification/',
  },
  {
    id: 'cka',
    name: 'Certified Kubernetes Administrator',
    issuer: 'Cloud Native Computing Foundation',
    year: 2023,
    credentialId: 'CKA-2300-1874',
    url: 'https://www.cncf.io/certification/cka/',
  },
  {
    id: 'terraform',
    name: 'HashiCorp Certified: Terraform Associate',
    issuer: 'HashiCorp',
    year: 2022,
    credentialId: 'HCTA-0042918',
    url: 'https://www.hashicorp.com/certification/terraform-associate',
  },
  {
    id: 'cpacc',
    name: 'Certified Professional in Accessibility Core Competencies',
    issuer: 'IAAP',
    year: 2020,
    credentialId: 'CPACC-5521',
    url: 'https://www.accessibilityassociation.org/certification',
  },
];
