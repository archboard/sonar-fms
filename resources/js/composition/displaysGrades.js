import { inject } from 'vue'

export default () => {
  const __ = inject('$translate')
  const displayLongGrade = grade => {
    const gradeInt = +grade

    if (gradeInt > 0) {
      return __('Grade :grade', { grade })
    }

    if (gradeInt === 0) {
      return __('Kindergarten')
    }

    return __('Pre-Kindergarten age :age', { age: (5 - Math.abs(gradeInt)) })
  }
  const displayShortGrade = grade => {
    const gradeInt = +grade

    if (gradeInt > 0) {
      return grade
    }

    if (gradeInt === 0) {
      return __('K')
    }

    return __('PK:age', { age: (5 - Math.abs(gradeInt)) })
  }

  return {
    displayShortGrade,
    displayLongGrade,
  }
}
