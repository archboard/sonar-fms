import { nanoid } from 'nanoid'

export default () => {
  const addMapFieldValue = (value = null) => {
    return {
      id: nanoid(),
      value,
      column: null,
      isManual: false,
    }
  }

  return {
    addMapFieldValue
  }
}
