import { cart } from "../../data/cart.js";

export function calculateCartQuantity(){
    let cartQuantity=0;
    cart.forEach((cartItem)=>{
        cartQuantity+=parseInt(cartItem.quantity);
      })
      return cartQuantity;
}