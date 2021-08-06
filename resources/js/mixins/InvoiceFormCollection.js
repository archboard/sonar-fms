import { PlusSmIcon } from '@heroicons/vue/solid'
import { TrashIcon } from '@heroicons/vue/outline'
import CardSectionHeader from '@/components/CardSectionHeader'
import HelpText from '@/components/HelpText'
import Error from '@/components/forms/Error'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import InputWrap from '@/components/forms/InputWrap'
import Fieldset from '@/components/forms/Fieldset'
import Select from '@/components/forms/Select'
import Input from '@/components/forms/Input'
import CurrencyInput from '@/components/forms/CurrencyInput'
import Button from '@/components/Button'
import FadeIn from '@/components/transitions/FadeIn'

export default {
  components: {
    FadeIn,
    Button,
    CurrencyInput,
    InputWrap,
    CardPadding,
    CardWrapper,
    FadeInGroup,
    Error,
    HelpText,
    CardSectionHeader,
    PlusSmIcon,
    TrashIcon,
    Select,
    Input,
    Fieldset,
  },
  props: {
    modelValue: Array,
    form: Object,
  },
  emits: ['update:modelValue'],
}
