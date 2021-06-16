<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Course
 *
 * @mixin IdeHelperCourse
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property string $name
 * @property int $sis_id
 * @property string|null $course_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCourseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 */
	class IdeHelperCourse extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Currency
 *
 * @mixin IdeHelperCurrency
 * @property int $id
 * @property string $code
 * @property string|null $number
 * @property int $digits
 * @property string|null $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereDigits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 */
	class IdeHelperCurrency extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 *
 * @mixin IdeHelperDepartment
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\DepartmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 */
	class IdeHelperDepartment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Fee
 *
 * @mixin IdeHelperFee
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property int|null $amount
 * @property int|null $fee_category_id
 * @property int|null $department_id
 * @property int|null $course_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\FeeCategory|null $feeCategory
 * @property-read mixed $amount_formatted
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\FeeFactory factory(...$parameters)
 * @method static Builder|Fee filter(array $filters)
 * @method static Builder|Fee newModelQuery()
 * @method static Builder|Fee newQuery()
 * @method static Builder|Fee query()
 * @method static Builder|Fee whereAmount($value)
 * @method static Builder|Fee whereCode($value)
 * @method static Builder|Fee whereCourseId($value)
 * @method static Builder|Fee whereCreatedAt($value)
 * @method static Builder|Fee whereDepartmentId($value)
 * @method static Builder|Fee whereDescription($value)
 * @method static Builder|Fee whereFeeCategoryId($value)
 * @method static Builder|Fee whereId($value)
 * @method static Builder|Fee whereName($value)
 * @method static Builder|Fee whereSchoolId($value)
 * @method static Builder|Fee whereTenantId($value)
 * @method static Builder|Fee whereUpdatedAt($value)
 */
	class IdeHelperFee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FeeCategory
 *
 * @mixin IdeHelperFeeCategory
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\FeeCategoryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereUpdatedAt($value)
 */
	class IdeHelperFeeCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Invoice
 *
 * @mixin IdeHelperInvoice
 * @property int $id
 * @property string $uuid
 * @property string|null $batch_id
 * @property string|null $import_id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $student_id
 * @property int|null $term_id
 * @property string $title
 * @property string|null $description
 * @property int|null $amount_due
 * @property int|null $remaining_balance
 * @property string $invoice_date
 * @property Carbon|null $available_at
 * @property Carbon|null $due_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $voided_at
 * @property bool $notify
 * @property Carbon|null $notify_at
 * @property Carbon|null $notified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $amount_due_formatted
 * @property-read bool $available
 * @property-read mixed $past_due
 * @property-read mixed $payment_made
 * @property-read mixed $remaining_balance_formatted
 * @property-read mixed $status_color
 * @property-read mixed $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItem[] $invoiceItems
 * @property-read int|null $invoice_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoicePaymentSchedule[] $invoicePaymentSchedules
 * @property-read int|null $invoice_payment_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoicePaymentTerm[] $invoicePaymentTerms
 * @property-read int|null $invoice_payment_terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceScholarship[] $invoiceScholarships
 * @property-read int|null $invoice_scholarships_count
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Tenant $tenant
 * @property-read \App\Models\Term|null $term
 * @method static \Database\Factories\InvoiceFactory factory(...$parameters)
 * @method static Builder|Invoice filter(array $filters)
 * @method static Builder|Invoice newModelQuery()
 * @method static Builder|Invoice newQuery()
 * @method static Builder|Invoice query()
 * @method static Builder|Invoice whereAmountDue($value)
 * @method static Builder|Invoice whereAvailableAt($value)
 * @method static Builder|Invoice whereBatchId($value)
 * @method static Builder|Invoice whereCreatedAt($value)
 * @method static Builder|Invoice whereDescription($value)
 * @method static Builder|Invoice whereDueAt($value)
 * @method static Builder|Invoice whereId($value)
 * @method static Builder|Invoice whereImportId($value)
 * @method static Builder|Invoice whereInvoiceDate($value)
 * @method static Builder|Invoice whereNotifiedAt($value)
 * @method static Builder|Invoice whereNotify($value)
 * @method static Builder|Invoice whereNotifyAt($value)
 * @method static Builder|Invoice wherePaidAt($value)
 * @method static Builder|Invoice whereRemainingBalance($value)
 * @method static Builder|Invoice whereSchoolId($value)
 * @method static Builder|Invoice whereStudentId($value)
 * @method static Builder|Invoice whereTenantId($value)
 * @method static Builder|Invoice whereTermId($value)
 * @method static Builder|Invoice whereTitle($value)
 * @method static Builder|Invoice whereUpdatedAt($value)
 * @method static Builder|Invoice whereUuid($value)
 * @method static Builder|Invoice whereVoidedAt($value)
 */
	class IdeHelperInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceImport
 *
 * @property int $id
 * @property int $school_id
 * @property int $user_id
 * @property string $file_path
 * @property array|null $mapping
 * @property int|null $total_records
 * @property int|null $imported_records
 * @property int|null $failed_records
 * @property \Illuminate\Support\Carbon|null $imported_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\School $school
 * @property-read \App\Models\User $user
 * @method static Builder|InvoiceImport filter(array $filters)
 * @method static Builder|InvoiceImport newModelQuery()
 * @method static Builder|InvoiceImport newQuery()
 * @method static Builder|InvoiceImport query()
 * @method static Builder|InvoiceImport whereCreatedAt($value)
 * @method static Builder|InvoiceImport whereFailedRecords($value)
 * @method static Builder|InvoiceImport whereFilePath($value)
 * @method static Builder|InvoiceImport whereId($value)
 * @method static Builder|InvoiceImport whereImportedAt($value)
 * @method static Builder|InvoiceImport whereImportedRecords($value)
 * @method static Builder|InvoiceImport whereMapping($value)
 * @method static Builder|InvoiceImport whereSchoolId($value)
 * @method static Builder|InvoiceImport whereTotalRecords($value)
 * @method static Builder|InvoiceImport whereUpdatedAt($value)
 * @method static Builder|InvoiceImport whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperInvoiceImport extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceItem
 *
 * @mixin IdeHelperInvoiceItem
 * @property int $id
 * @property string $uuid
 * @property string $invoice_uuid
 * @property string|null $batch_id
 * @property int|null $fee_id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $amount_per_unit
 * @property int|null $amount
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fee|null $fee
 * @property-read mixed $amount_formatted
 * @property-read mixed $amount_per_unit_formatted
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereAmountPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereUuid($value)
 */
	class IdeHelperInvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoicePaymentSchedule
 *
 * @mixin IdeHelperInvoicePaymentSchedule
 * @property int $id
 * @property string $uuid
 * @property string|null $invoice_uuid
 * @property string|null $batch_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoicePaymentTerm[] $invoicePaymentTerms
 * @property-read int|null $invoice_payment_terms_count
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentSchedule whereUuid($value)
 */
	class IdeHelperInvoicePaymentSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoicePaymentTerm
 *
 * @mixin IdeHelperInvoicePaymentTerm
 * @property int $id
 * @property string $uuid
 * @property string|null $invoice_uuid
 * @property string|null $invoice_payment_schedule_uuid
 * @property string|null $batch_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property string|null $notified_at
 * @property bool $notify
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\InvoicePaymentSchedule|null $invoicePaymentSchedule
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereInvoicePaymentScheduleUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentTerm whereUuid($value)
 */
	class IdeHelperInvoicePaymentTerm extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceScholarship
 *
 * @mixin IdeHelperInvoiceScholarship
 * @property int $id
 * @property string $uuid
 * @property string $invoice_uuid
 * @property string|null $batch_id
 * @property int|null $scholarship_id
 * @property string $name
 * @property string|null $percentage
 * @property int|null $amount
 * @property string|null $resolution_strategy
 * @property int|null $calculated_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItem[] $appliesTo
 * @property-read int|null $applies_to_count
 * @property-read mixed $amount_formatted
 * @property-read mixed $calculated_amount_formatted
 * @property-read mixed $percentage_decimal
 * @property-read mixed $percentage_formatted
 * @property-read \App\Models\Invoice $invoice
 * @property-read \App\Models\Scholarship|null $scholarship
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereCalculatedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereResolutionStrategy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereScholarshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereUuid($value)
 */
	class IdeHelperInvoiceScholarship extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceTemplate
 *
 * @mixin IdeHelperInvoiceTemplate
 * @property int $id
 * @property int $school_id
 * @property int|null $user_id
 * @property string $name
 * @property array $template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\School $school
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\InvoiceTemplateFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTemplate whereUserId($value)
 */
	class IdeHelperInvoiceTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Scholarship
 *
 * @mixin IdeHelperScholarship
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property string $name
 * @property string|null $description
 * @property float|null $percentage
 * @property int|null $amount
 * @property string|null $resolution_strategy
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $percentage_formatted
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\ScholarshipFactory factory(...$parameters)
 * @method static Builder|Scholarship filter(array $filters)
 * @method static Builder|Scholarship newModelQuery()
 * @method static Builder|Scholarship newQuery()
 * @method static Builder|Scholarship query()
 * @method static Builder|Scholarship whereAmount($value)
 * @method static Builder|Scholarship whereCreatedAt($value)
 * @method static Builder|Scholarship whereDescription($value)
 * @method static Builder|Scholarship whereId($value)
 * @method static Builder|Scholarship whereName($value)
 * @method static Builder|Scholarship wherePercentage($value)
 * @method static Builder|Scholarship whereResolutionStrategy($value)
 * @method static Builder|Scholarship whereSchoolId($value)
 * @method static Builder|Scholarship whereTenantId($value)
 * @method static Builder|Scholarship whereUpdatedAt($value)
 */
	class IdeHelperScholarship extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\School
 *
 * @mixin IdeHelperSchool
 * @property int $id
 * @property int $tenant_id
 * @property int $sis_id
 * @property int|null $school_number
 * @property string $name
 * @property int|null $high_grade
 * @property int|null $low_grade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $use_thousands_separator
 * @property bool $active
 * @property int|null $currency_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Course[] $courses
 * @property-read int|null $courses_count
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Fee[] $fees
 * @property-read int|null $fees_count
 * @property-read mixed $grade_levels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceImport[] $invoiceImports
 * @property-read int|null $invoice_imports_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceTemplate[] $invoiceTemplates
 * @property-read int|null $invoice_templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scholarship[] $scholarships
 * @property-read int|null $scholarships_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Term[] $terms
 * @property-read int|null $terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|School active()
 * @method static \Database\Factories\SchoolFactory factory(...$parameters)
 * @method static Builder|School newModelQuery()
 * @method static Builder|School newQuery()
 * @method static Builder|School query()
 * @method static Builder|School whereActive($value)
 * @method static Builder|School whereCreatedAt($value)
 * @method static Builder|School whereCurrencyId($value)
 * @method static Builder|School whereHighGrade($value)
 * @method static Builder|School whereId($value)
 * @method static Builder|School whereLowGrade($value)
 * @method static Builder|School whereName($value)
 * @method static Builder|School whereSchoolNumber($value)
 * @method static Builder|School whereSisId($value)
 * @method static Builder|School whereTenantId($value)
 * @method static Builder|School whereUpdatedAt($value)
 * @method static Builder|School whereUseThousandsSeparator($value)
 */
	class IdeHelperSchool extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Section
 *
 * @mixin IdeHelperSection
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int|null $term_id
 * @property int $course_id
 * @property int $user_id
 * @property int $sis_id
 * @property string|null $section_number
 * @property string|null $expression
 * @property string|null $external_expression
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereExternalExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSectionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereUserId($value)
 */
	class IdeHelperSection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Student
 *
 * @mixin IdeHelperStudent
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $sis_id
 * @property string|null $student_number
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property int|null $grade_level
 * @property bool $enrolled
 * @property int $enroll_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $preferred_name
 * @property \Illuminate\Support\Carbon|null $current_entry_date
 * @property \Illuminate\Support\Carbon|null $current_exit_date
 * @property \Illuminate\Support\Carbon|null $initial_district_entry_date
 * @property \Illuminate\Support\Carbon|null $initial_school_entry_date
 * @property string|null $initial_district_grade_level
 * @property string|null $initial_school_grade_level
 * @property-read mixed $full_name
 * @property-read mixed $grade_level_formatted
 * @property-read mixed $grade_level_short_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $guardians
 * @property-read int|null $guardians_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\StudentFactory factory(...$parameters)
 * @method static Builder|Student filter(array $filters)
 * @method static Builder|Student newModelQuery()
 * @method static Builder|Student newQuery()
 * @method static Builder|Student query()
 * @method static Builder|Student sisId($sisId)
 * @method static Builder|Student whereCreatedAt($value)
 * @method static Builder|Student whereCurrentEntryDate($value)
 * @method static Builder|Student whereCurrentExitDate($value)
 * @method static Builder|Student whereEmail($value)
 * @method static Builder|Student whereEnrollStatus($value)
 * @method static Builder|Student whereEnrolled($value)
 * @method static Builder|Student whereFirstName($value)
 * @method static Builder|Student whereGradeLevel($value)
 * @method static Builder|Student whereId($value)
 * @method static Builder|Student whereInitialDistrictEntryDate($value)
 * @method static Builder|Student whereInitialDistrictGradeLevel($value)
 * @method static Builder|Student whereInitialSchoolEntryDate($value)
 * @method static Builder|Student whereInitialSchoolGradeLevel($value)
 * @method static Builder|Student whereLastName($value)
 * @method static Builder|Student wherePreferredName($value)
 * @method static Builder|Student whereSchoolId($value)
 * @method static Builder|Student whereSisId($value)
 * @method static Builder|Student whereStudentNumber($value)
 * @method static Builder|Student whereTenantId($value)
 * @method static Builder|Student whereUpdatedAt($value)
 */
	class IdeHelperStudent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StudentSelection
 *
 * @mixin IdeHelperStudentSelection
 * @property int $school_id
 * @property int $student_id
 * @property int $user_id
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\User $user
 * @method static Builder|StudentSelection newModelQuery()
 * @method static Builder|StudentSelection newQuery()
 * @method static Builder|StudentSelection query()
 * @method static Builder|StudentSelection student($studentId)
 * @method static Builder|StudentSelection whereSchoolId($value)
 * @method static Builder|StudentSelection whereStudentId($value)
 * @method static Builder|StudentSelection whereUserId($value)
 */
	class IdeHelperStudentSelection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SyncTime
 *
 * @mixin IdeHelperSyncTime
 * @property int $id
 * @property int $tenant_id
 * @property int $hour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereUpdatedAt($value)
 */
	class IdeHelperSyncTime extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Tenant
 *
 * @mixin IdeHelperTenant
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string|null $ps_url
 * @property string|null $ps_client_id
 * @property string|null $ps_secret
 * @property bool $allow_password_auth
 * @property string|null $subscription_started_at
 * @property string|null $subscription_expires_at
 * @property string $license
 * @property string $sis_provider
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sync_notification_emails
 * @property bool $allow_oidc_login
 * @property string|null $smtp_host
 * @property string|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property string|null $smtp_from_name
 * @property string|null $smtp_from_address
 * @property string|null $smtp_encryption
 * @property string|null $batch_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Department[] $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeeCategory[] $feeCategories
 * @property-read int|null $fee_categories_count
 * @property-read string $sis
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\School[] $schools
 * @property-read int|null $schools_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SyncTime[] $syncTimes
 * @property-read int|null $sync_times_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Spatie\Multitenancy\TenantCollection|static[] all($columns = ['*'])
 * @method static \Database\Factories\TenantFactory factory(...$parameters)
 * @method static \Spatie\Multitenancy\TenantCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereAllowOidcLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereAllowPasswordAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSisProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpFromAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSyncNotificationEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
 */
	class IdeHelperTenant extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Term
 *
 * @mixin IdeHelperTerm
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $sis_id
 * @property int $sis_assigned_id
 * @property string $name
 * @property string $abbreviation
 * @property int $start_year
 * @property int $portion
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_current
 * @property-read mixed $school_years
 * @method static \Database\Factories\TermFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Term newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term query()
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term wherePortion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSisAssignedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereStartYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereUpdatedAt($value)
 */
	class IdeHelperTerm extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property int $tenant_id
 * @property int|null $sis_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $password
 * @property int|null $school_id
 * @property string|null $timezone
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $contact_id
 * @property int|null $guardian_id
 * @property bool $manages_tenancy
 * @property string $locale
 * @property-read \Illuminate\Database\Eloquent\Collection|\Silber\Bouncer\Database\Ability[] $abilities
 * @property-read int|null $abilities_count
 * @property-read mixed $date_factory
 * @property-read string $full_name
 * @property-read array $school_permissions
 * @property-read mixed $student_selection
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Silber\Bouncer\Database\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\School|null $school
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\School[] $schools
 * @property-read int|null $schools_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StudentSelection[] $studentSelections
 * @property-read int|null $student_selections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User filter(array $filters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCan(string $ability)
 * @method static Builder|User whereContactId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereGuardianId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIs($role)
 * @method static Builder|User whereIsAll($role)
 * @method static Builder|User whereIsNot($role)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereLocale($value)
 * @method static Builder|User whereManagesTenancy($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereSchoolId($value)
 * @method static Builder|User whereSisId($value)
 * @method static Builder|User whereTenantId($value)
 * @method static Builder|User whereTimezone($value)
 * @method static Builder|User whereUpdatedAt($value)
 */
	class IdeHelperUser extends \Eloquent {}
}

